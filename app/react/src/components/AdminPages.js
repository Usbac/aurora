import React, { useState } from 'react';
import { IconBook, IconHome, IconImage, IconLink, IconLogout, IconMoon, IconPencil, IconSettings, IconSun, IconTag, IconUser, IconWindow } from '../utils/icons';
import { Link, Navigate, Outlet, useNavigate } from 'react-router-dom';
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
                <img src="/public/assets/logo.svg"/>
                <h1>Aurora</h1>
            </header>
            <div class="admin-options">
                <Link to="/console/dashboard">
                    <IconHome/> Dashboard
                </Link>
                <a href="/" target="_blank">
                    <IconWindow/> View Site
                </a>
                <Link to="/console/pages" data-separator>
                    <IconBook/> Pages
                </Link>
                <Link to="/console/posts">
                    <IconPencil/> Posts
                </Link>
                <Link to="/console/tags">
                    <IconTag/> Tags
                </Link>
                <Link to="/console/media">
                    <IconImage/> Media
                </Link>
                <Link to="/console/users">
                    <IconUser/> Users
                </Link>
                <Link to="/console/links">
                    <IconLink/> Links
                </Link>
                <Link to="/console/settings">
                    <IconSettings/> Settings
                </Link>
            </div>
            <div class="current-user">
                <Link to={`/console/users/edit?id=${user?.id}`} title={user?.name}>
                    <img src={user?.image ? user.image : '/public/assets/no-image.svg'} className="empty-img"/>
                </Link>
                <div id="toggle-theme" class="pointer" title="Switch theme" onClick={toggleTheme} data-theme={theme}>
                    {theme == 'light' ? <IconMoon/> : <IconSun/>}
                </div>
                <div class="pointer" title="Logout" onClick={logout}>
                    <IconLogout/>
                </div>
            </div>
        </nav>
        <Outlet context={{ user: user, settings: settings }}/>
    </div>;
};