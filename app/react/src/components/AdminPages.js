import React, { useState } from 'react';
import { IconBook, IconHome, IconImage, IconLink, IconLogout, IconMoon, IconPencil, IconSettings, IconSun, IconTag, IconUser, IconWindow } from '../utils/icons';
import { Navigate, Outlet, useNavigate } from 'react-router-dom';
import { useElement } from '../utils/utils';

export default function AdminPages() {
    const dark_theme_element = document.getElementById('css-dark');
    const user = useElement('/api/v2/me');
    const settings = useElement('/api/v2/settings');
    const [ theme, setTheme ] = useState(dark_theme_element?.hasAttribute('disabled') ? 'light' : 'dark');
    const navigate = useNavigate();

    const toggleTheme = () => {
        const is_light_enabled = dark_theme_element.toggleAttribute('disabled');
        setTheme(is_light_enabled ? 'light' : 'dark');
        document.cookie = 'theme=' + (is_light_enabled ? 'light' : 'dark') + ';path=/';
    };

    const logout = () => {
        localStorage.removeItem('auth_token');
        navigate('/console', { replace: true });
    };

    if (user === null) {
        return <Navigate to="/console" replace/>;
    }

    return <div className="admin">
        <nav>
            <header>
                <img src="/public/assets/logo.svg" alt=""/>
                <h1>Aurora</h1>
            </header>
            <div class="admin-options">
                <a href="/console/dashboard">
                    <IconHome/> Dashboard
                </a>
                <a href="/" target="_blank">
                    <IconWindow/> View Site
                </a>
                <a href="/console/pages" data-separator>
                    <IconBook/> Pages
                </a>
                <a href="/console/posts">
                    <IconPencil/> Posts
                </a>
                <a href="/console/tags">
                    <IconTag/> Tags
                </a>
                <a href="/console/media">
                    <IconImage/> Media
                </a>
                <a href="/console/users">
                    <IconUser/> Users
                </a>
                <a href="/console/links">
                    <IconLink/> Links
                </a>
                <a href="/console/settings">
                    <IconSettings/> Settings
                </a>
            </div>
            <div class="current-user">
                <a href={`/console/users/edit?id=${user?.id}`} title={user?.name}>
                    <img src={user?.image ? user.image : '/public/assets/no-image.svg'} className="empty-img"/>
                </a>
                <div id="toggle-theme" class="pointer" title="Switch theme" onClick={toggleTheme} data-theme={theme}>
                    {theme == 'light' ? <IconMoon/> : <IconSun/>}
                </div>
                <div href="/console/logout" class="pointer" title="Logout" onClick={logout}>
                    <IconLogout/>
                </div>
            </div>
        </nav>
        <Outlet context={{ user: user, settings: settings }}/>
    </div>;
};