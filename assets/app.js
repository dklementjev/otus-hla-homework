import './styles/app.css';

import {LoginPage} from "./login-page";
import {Container} from "./utils/Container";
import {Urlconf} from "./utils/Urlconf";
import {SessionStorage} from "./models/token-storage/SessionStoage";
import {Auth} from "./models/Auth";
import {AuthAPI} from "./api/AuthAPI";

const container = Container.getInstance();
container.set(
    'urlconf',
    () => new Urlconf(window.apiBaseUrl, window.urlconf)
);
container.set(
    'token-storage',
    () => new SessionStorage('authToken')
);
container.set(
    'auth',
    () => new Auth(container.get('token-storage'))
);
container.set(
    'api.auth',
    () => new AuthAPI(container.get('urlconf'))
);

const loginPage = new LoginPage(
    container.get('api.auth'),
    container.get('auth')
);
loginPage.render();
