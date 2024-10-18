FROM citusdata/membership-manager:0.3.0

RUN apk add --no-cache bash

ADD --chmod=0755 ./citus-manager-entrypoint.sh /manager-entrypoint.sh

ENTRYPOINT ["/manager-entrypoint.sh"]
