import React, { useState } from 'react';
import { IconBook, IconHome, IconImage, IconLink, IconLogout, IconMoon, IconPencil, IconSettings, IconSun, IconTag, IconUser, IconWindow } from '../utils/icons';
import { Link, Navigate, Outlet, useNavigate } from 'react-router-dom';
import { getContentUrl, LoadingPage, makeRequest, useElement } from '../utils/utils';
import { useI18n } from '../providers/I18nProvider';

export default function AdminPages() {
    const dark_theme_element = document.getElementById('css-dark');
    const [ user, fetch_user ] = useElement('/api/me');
    const [ settings, fetch_settings ] = useElement('/api/settings');
    const [ theme, setTheme ] = useState(dark_theme_element?.hasAttribute('disabled') ? 'light' : 'dark');
    const navigate = useNavigate();
    const { t } = useI18n();

    const toggleTheme = () => {
        const is_light_enabled = dark_theme_element.toggleAttribute('disabled');
        setTheme(is_light_enabled ? 'light' : 'dark');
        document.cookie = 'theme=' + (is_light_enabled ? 'light' : 'dark') + ';path=/';
    };

    const logout = () => {
        makeRequest({
            method: 'POST',
            url: '/api/logout',
        }).catch(err => alert('Error during logout: ' + err))
            .finally(() => navigate('/admin', { replace: true }));
    };

    if (user === null) {
        return <Navigate to="/admin" replace/>;
    }

    return <div className="admin">
        <nav>
            <header>
                <img src="/public/assets/logo.svg"/>
                <h1>Aurora</h1>
            </header>
            <div class="admin-options">
                <Link to="/admin/dashboard">
                    <IconHome/> {t('dashboard')}
                </Link>
                <a href="/" target="_blank">
                    <IconWindow/> {t('view_site')}
                </a>
                <Link to="/admin/pages" data-separator>
                    <IconBook/> {t('pages')}
                </Link>
                <Link to="/admin/posts">
                    <IconPencil/> {t('posts')}
                </Link>
                <Link to="/admin/tags">
                    <IconTag/> {t('tags')}
                </Link>
                <Link to="/admin/media">
                    <IconImage/> {t('media')}
                </Link>
                <Link to="/admin/users">
                    <IconUser/> {t('users')}
                </Link>
                <Link to="/admin/links">
                    <IconLink/> {t('links')}
                </Link>
                <Link to="/admin/settings">
                    <IconSettings/> {t('settings')}
                </Link>
            </div>
            <div class="current-user">
                <Link to={`/admin/users/edit?id=${user?.id}`} title={user?.name}>
                    <img src={user?.image ? getContentUrl(user.image) : '/public/assets/no-image.svg'} className={!user?.image ? 'empty-img' : ''}/>
                </Link>
                <div id="toggle-theme" class="pointer" title={t('switch_theme')} onClick={toggleTheme} data-theme={theme}>
                    {theme == 'light' ? <IconMoon/> : <IconSun/>}
                </div>
                <div class="pointer" title={t('logout')} onClick={logout}>
                    <IconLogout/>
                </div>
            </div>
        </nav>
        {user && settings ? <Outlet context={{ user: user, fetch_user: fetch_user, settings: settings, fetch_settings: fetch_settings, theme: theme }}/> : <LoadingPage/>}
    </div>;
};