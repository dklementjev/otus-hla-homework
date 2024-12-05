-- Logger
require('strict').on()

local app_name = "default"
local Log = require('log')
local log = Log.new(app_name)
local UUID = require('uuid')
local Datetime = require('datetime');
local Fun = require('fun')
local Json = require('json')

-- Load schema
local yaml = require('yaml')
local ddl = require('ddl')

box.cfg{}

local fh = io.open('./apps/' .. app_name .. '/schema.yaml', 'r')
local schema = yaml.decode(fh:read('*all'))
fh:close()
local ok, err = ddl.check_schema(schema)
if not ok then
    print(err)
    os.exit(1)
end
local ok, err = ddl.set_schema(schema)
if not ok then
    print(err)
    os.exit(2)
end
log.info(app_name .. ' schema loaded')

-- UDF
local Subscription = {}
function Subscription.eq (a, b)
  return a.uuid == b.uuid
end
function Subscription.isPM(s)
  return not s.is_groupchat
end
function Subscription.getOtherUserId(s)
  return s.user_id
end
function Subscription.toDB(rawSubscription)
  return {uuid=rawSubscription.uuid or UUID.new(), is_groupchat=rawSubscription.is_groupchat, user_id=rawSubscription.user_id, dialog_id=rawSubscription.dialog_id}
end
function Subscription.toJSON (s)
  return Json.encode(s)
end

local Dialog = {}
function Dialog.toJSON  (d)
  return {
    id=d.id,
    uuid=tostring(d.uuid),
    is_groupchat=d.is_groupchat,
    created_at=tostring(d.created_at),
    participants=d.participants
  }
end

local DialogMessage = {}
function DialogMessage.toJSON(dm)
  return {
    id=dm.id,
    uuid=tostring(dm.uuid),
    user_id=dm.user_id,
    dialog_id=dm.dialog_id,
    message=dm.message,
    created_at=tostring(dm.created_at)
  }
end

function setupUsersUDFs()
  local users_mt = getmetatable(box.space.users)

  users_mt.getById = function (self, user_id)
    local user
    local flog = Log.new('UDF: users.getById')

    flog.info("user_id=" .. user_id)
    local users = box.space.users.index.user_id:select(user_id)
    if #users > 0 then
      user = users[1]
    else
      flog.info("adding user")
      user = box.space.users:insert{user_id, UUID.new(), nil, {}}
    end
    flog.info(user)

    return user
  end

  users_mt.setNickname = function (self, user_id, nickname)
    self:update({user_id}, {{'=', 'nickname', nickname}})
  end

  users_mt.addSubscription = function (self, user, subscription)
    local user_subscriptions = user:tomap().subscriptions
    table.insert(user_subscriptions, subscription)
    self:update({user.id}, {{'=', 'subscriptions', user_subscriptions}})
  end

  users_mt.removeSubscription = function (self, user, subscription)
    local old_user_subscriptions = user:tomap().subscriptions
    local new_user_subscriptions = fun.filter(Subscription.eq, old_user_subscriptions)
    self:update(user.id, {{'=', 'subscriptions', new_user_subscriptions}})
  end

  -- TODO: remove ?
  users_mt.getPMSubscription = function (self, user, other_user)
    local flog = Log.new("UDF: getPMSubscription")

    flog.info('Running for ' .. user.id .. ', ' .. other_user.id)

    for i in ipairs(user:tomap().subscriptions) do
      local subscription = user.subscriptions[i]

      if Subscription.isPM(subscription) and Subscription.getOtherUserId(subscription) == other_user.id then
        flog.info('Subscription found')
        return subscription
      end
    end

    flog.info('Subscription NOT found')
    return nil
  end
end

function setupDialogUDFs()
  local dialogs_mt = getmetatable(box.space.dialogs)

  -- TODO: API
  dialogs_mt.getById = function (self, dialog_id)
    local flog = Log.new('dialogs.getById')

    flog.debug('Running for ' .. dialog_id)
    dialog = self.index.dialog_id:get(dialog_id)
    flog.debug(dialog)

    return dialog
  end

  -- TODO: API
  dialogs_mt.create = function (self)
    local flog = Log.new('dialogs.create')
    local dialog_id = box.sequence.dialog_id_seq:next()
    local now = Datetime.now()

    return self:insert{dialog_id, UUID.new(), false, now, {}}
  end

  -- TODO: API
  dialogs_mt.getPMForUsers = function (self, user_id, other_user_id)
    local flog = Log.new('dialogs.getPMForUsers')

    flog.info('Called with ' .. user_id .. ', ' .. other_user_id)

    local user = box.space.users:getById(user_id)
    local other_user = box.space.users:getById(other_user_id)
    local pmSubscription = box.space.users:getPMSubscription(user, other_user)

    flog.info(pmSubscription)

    return Dialog.toJSON(self:get(pmSubscription.dialog_id))
  end

  -- TODO: API
  dialogs_mt.createPMForUsers = function (self, user_id, other_user_id)
    local flog = Log.new('dialogs.createPMForUsers')

    flog.info('Called with ' .. user_id .. ', ' ..other_user_id)

    local participants = {user_id, other_user_id}
    local user = box.space.users:getById(user_id)
    local other_user = box.space.users:getById(other_user_id)
    local dialog = self:create()

    self:update({dialog.id}, {{'=', 'is_groupchat', false}, {'=', 'participants', participants}})

    box.space.users:addSubscription(user, Subscription.toDB({is_groupchat=false, user_id=other_user.id, dialog_id=dialog.id}))
    box.space.users:addSubscription(other_user, Subscription.toDB({is_groupchat=false, user_id=user.id, dialog_id=dialog.id}))

    return Dialog.toJSON(self:get(dialog.id))
  end
end

function setupMessagesUDFs()
  local messages_mt = getmetatable(box.space.messages)

  -- TODO: API
  messages_mt.create = function (self, rawUUID, userId, dialogId, messageText, rawCreatedAt)
    local messageId = box.sequence.message_id_seq:next()
    local uuid = UUID.fromstr(rawUUID)
    local createdAt = Datetime.parse(rawCreatedAt)

    return DialogMessage.toJSON(self:insert{messageId, uuid, userId, dialogId, messageText, createdAt})
  end

  -- TODO: API
  messages_mt.findByDialogId = function (self, dialog_id, limit)
    local messages = box.space.messages.index.dialog_messages:select({dialog_id}, {iterator=box.index.LE, limit=limit or 100})

    return Fun.map(DialogMessage.toJSON, messages):totable()
  end
end

function setupUDFs()
  setupUsersUDFs()
  setupDialogUDFs()
  setupMessagesUDFs()
end


-- Setup user-defined functions
log.info('Setting up UDFs')
setupUDFs()

-- Run app
log.info(app_name .. ' app loaded')
