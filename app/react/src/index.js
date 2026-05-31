import React, { useState } from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter, Route, Routes } from 'react-router-dom';
import { I18nProvider } from './providers/I18nProvider';
import { IconBook, IconHome, IconImage, IconLink, IconLogout, IconMoon, IconPencil, IconSettings, IconSun, IconTag, IconUser, IconWindow } from './utils/icons';
import { Link as RouterLink, Navigate, Outlet, useLocation, useNavigate } from 'react-router-dom';
import { getContentUrl, LoadingPage, useApi, useElement } from './utils/utils';
import { useI18n } from './providers/I18nProvider';
import NewPassword from './pages/NewPassword';
import Login from './pages/Login';
import Dashboard from './pages/Dashboard';
import Settings from './pages/Settings';
import Links from './pages/tables/Links';
import Link from './pages/Link';
import Tags from './pages/tables/Tags';
import Media from './pages/tables/Media';
import Pages from './pages/tables/Pages';
import Posts from './pages/tables/Posts';
import Users from './pages/tables/Users';
import Tag from './pages/Tag';
import Page from './pages/Page';
import Post from './pages/Post';
import User from './pages/User';
import Information from './pages/Information';

const AdminPages = () => {
    const dark_theme_element = document.getElementById('css-dark');
    const [ user, fetch_user ] = useElement('/api/me');
    const [ settings, fetch_settings ] = useElement('/api/settings');
    const [ theme, setTheme ] = useState(dark_theme_element?.hasAttribute('disabled') ? 'light' : 'dark');
    const navigate = useNavigate();
    const location = useLocation();
    const { t, language, getLanguages, changeLanguage } = useI18n();
    const { request } = useApi();

    const toggleTheme = () => {
        const is_light_enabled = dark_theme_element.toggleAttribute('disabled');
        setTheme(is_light_enabled ? 'light' : 'dark');
        document.cookie = 'theme=' + (is_light_enabled ? 'light' : 'dark') + ';path=/';
    };

    const logout = () => {
        request({
            method: 'POST',
            url: '/api/logout',
        }).finally(() => navigate('/admin', { replace: true }));
    };

    if (user === null) {
        return <Navigate to="/admin" replace/>;
    }

    const NavLink = ({ to, Icon, textKey, ...props }) => <RouterLink to={to} data-checked={location.pathname.startsWith(to)} {...props}>
        <Icon/> {t(textKey)}
    </RouterLink>

    return <div className="admin">
        <nav>
            <header>
                <img src="/public/assets/logo.svg"/>
                <h1>Aurora</h1>
            </header>
            <div className="admin-options">
                <NavLink to="/admin/dashboard" Icon={IconHome} textKey="dashboard"/>
                <a href="/" target="_blank"><IconWindow/> {t('view_site')}</a>
                <NavLink to="/admin/pages" Icon={IconBook} textKey="pages" data-separator/>
                <NavLink to="/admin/posts" Icon={IconPencil} textKey="posts"/>
                <NavLink to="/admin/tags" Icon={IconTag} textKey="tags"/>
                <NavLink to="/admin/media" Icon={IconImage} textKey="media"/>
                <NavLink to="/admin/users" Icon={IconUser} textKey="users"/>
                <NavLink to="/admin/links" Icon={IconLink} textKey="links"/>
                {user?.actions?.edit_settings && <NavLink to="/admin/settings" Icon={IconSettings} textKey="settings"/>}
            </div>
            <div className="current-user">
                <RouterLink to={`/admin/users/edit?id=${user?.id}`} title={user?.name}>
                    <img src={user?.image ? getContentUrl(user.image) : '/public/assets/no-image.svg'} className={!user?.image ? 'empty-img' : ''}/>
                </RouterLink>
                <div id="toggle-theme" className="pointer" title={t('switch_theme')} onClick={toggleTheme} data-theme={theme}>
                    {theme == 'light' ? <IconMoon/> : <IconSun/>}
                </div>
                <select value={language} onChange={e => changeLanguage(e.target.value)}>
                    {getLanguages().map((lang) => <option key={lang} value={lang}>{lang.toUpperCase()}</option>)}
                </select>
                <div className="pointer" title={t('logout')} onClick={logout}>
                    <IconLogout/>
                </div>
            </div>
        </nav>
        {user && settings ? <Outlet context={{ user: user, fetch_user: fetch_user, settings: settings, fetch_settings: fetch_settings, theme: theme }}/> : <LoadingPage/>}
        <div className="nav-background" onClick={() => document.body.toggleAttribute('data-nav-open')}></div>
    </div>;
};

const App = () => {
    return <BrowserRouter>
        <I18nProvider defaultLanguage={document.documentElement.lang}>
            <Routes>
                <Route path="/admin/new_password" element={<NewPassword/>}/>
                <Route path="/admin" element={<Login/>}/>
                <Route path="/admin" element={<AdminPages/>}>
                    <Route path="dashboard" element={<Dashboard/>}/>
                    <Route path="pages" element={<Pages/>}/>
                    <Route path="posts" element={<Posts/>}/>
                    <Route path="users" element={<Users/>}/>
                    <Route path="media" element={<Media/>}/>
                    <Route path="links" element={<Links/>}/>
                    <Route path="tags" element={<Tags/>}/>
                    <Route path="settings" element={<Settings/>}/>
                    <Route path="pages/edit" element={<Page/>}/>
                    <Route path="posts/edit" element={<Post/>}/>
                    <Route path="links/edit" element={<Link/>}/>
                    <Route path="tags/edit" element={<Tag/>}/>
                    <Route path="users/edit" element={<User/>}/>
                    <Route path="*" element={<Information title="404" subtitle="Not found"/>}/>
                </Route>
            </Routes>
        </I18nProvider>
    </BrowserRouter>;
};

createRoot(document.getElementById('root')).render(<App/>);
