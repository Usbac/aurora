import React, { useEffect, useState } from 'react';
import { getUrl, ImageDialog, Input, LoadingPage, MenuButton, Switch, Textarea, useApi, useRequest, formatDate, getRoleTitle, getSlug, getDeviceInfo, getDeviceType, Dropdown } from '../utils/utils';
import { IconDesktop, IconEye, IconInfo, IconKey, IconMobile, IconTrash, IconUsers } from '../utils/icons';
import { useLocation, useNavigate, useOutletContext } from 'react-router-dom';
import { useI18n } from '../providers/I18nProvider';

const Session = ({ id, userAgent, ip, createdAt, updatedAt, current, fetchSessions, t }) => {
    const [ revoking, setRevoking ] = useState(false);
    const { request } = useApi();
    const { theme } = useOutletContext();
    const device = getDeviceInfo(userAgent);
    const type = getDeviceType(userAgent);
    let Icon = IconKey;

    if (!userAgent) {
        Icon = IconKey;
    } else if (type == 'Desktop') {
        Icon = IconDesktop;
    } else if (type == 'Mobile' || type == 'Tablet') {
        Icon = IconMobile;
    }

    const revoke = () => {
        setRevoking(true);
        request({
            method: 'DELETE',
            url: '/api/me/sessions/' + id,
        }).then(res => {
            alert(t(res?.data?.success ? 'session_deleted_successfully' : 'error_occurred'));
            return fetchSessions();
        }).finally(() => setRevoking(false));
    };

    return (<div className="session">
        <Icon fill={theme != 'dark' ? 'black' : 'white'}/>
        <div>
            <b>
                {device.os}{device.version ? ` (${device.version})` : ''}
                {current && <span className="title-label green">{t('current_session')}</span>}
                {userAgent && <Dropdown trigger={<IconInfo fill={theme != 'dark' ? 'black' : 'white'}/>} panelClassName="dropdown-content" align="center" children={<span>{userAgent}</span>}/>}
            </b>
            <p>{t('registered')}: {formatDate(createdAt)}</p>
            <p>{t('last_active')}: {formatDate(updatedAt)}</p>
            {ip && <p>IP: {ip}</p>}
        </div>
        {!current && <button type="button" className="delete" disabled={revoking} onClick={() => revoke()}>{t('revoke_session')}</button>}
    </div>);
};

export default function User() {
    const { user, settings, fetch_user } = useOutletContext();
    const [ data, setData ] = useState(undefined);
    const [ open_image_dialog, setOpenImageDialog ] = useState(false);
    const { data: roles_req, is_loading: is_loading_roles, fetch: fetch_roles } = useRequest({
        method: 'GET',
        url: '/api/roles',
    });
    const { data: sessions_req, fetch: fetch_sessions } = useRequest({
        method: 'GET',
        url: '/api/me/sessions',
    });
    const location = useLocation();
    const navigate = useNavigate();
    const params = new URLSearchParams(location.search);
    const [ id, setId ] = useState(params.get('id'));
    const roles = Array.isArray(roles_req?.data) ? roles_req.data : [];
    const sessions = sessions_req?.data ?? [];
    const is_current_user = id && user?.id == id;
    const { t } = useI18n();
    const { request } = useApi();

    useEffect(() => {
        fetch_roles();
        fetch_sessions();

        if (id) {
            request({
                method: 'GET',
                url: `/api/users?id=${id}`,
            }).then(res => setData(res?.data?.data[0] ?? null));
        } else {
            setData({});
        }
    }, [ id ]);

    const remove = () => {
        if (confirm(t('confirm_delete_user', data.name))) {
            request({
                method: 'DELETE',
                url: '/api/users',
                data: { id: id },
            }).then(res => {
                if (res?.data?.success) {
                    alert(t('user_deleted_successfully'));
                    navigate('/admin/users', { replace: true });
                } else {
                    alert(t('error_deleting_user'));
                }
            });
        }
    };

    const impersonate = () => {
        if (confirm(t('confirm_impersonate_user'))) {
            request({
                method: 'POST',
                url: '/api/users/impersonate',
                data: { id: id },
            }).then(res => {
                if (!res?.data?.success) {
                    alert(t('error_impersonating_user'));
                } else {
                    fetch_user();
                }
            });
        }
    };

    const submit = e => {
        e.preventDefault();
        request({
            method: 'POST',
            url: '/api/users' + (id ? `?id=${id}` : ''),
            data: data,
        }).then(res => {
            if (res?.data?.success) {
                alert(t('user_saved_successfully'));
                if (is_current_user) {
                    fetch_user();
                }

                if (res?.data?.id) {
                    navigate(`/admin/users/edit?id=${res.data.id}`, { replace: true });
                    setId(res.data.id);
                }
            } else {
                const parts = (res?.data?.errors ?? []).map(c => t(c));
                alert(parts.length ? parts.join('\n') : t('error_generic'));
            }
        });
    };

    if (data === undefined) {
        return <LoadingPage/>;
    }

    if (!data) {
        return <>{t('error')}</>;
    }

    return (<form id="user-form" className="content" onSubmit={submit}>
        {open_image_dialog && <ImageDialog onSave={path => { setOpenImageDialog(false); setData({ ...data, image: path }); }} onClose={() => setOpenImageDialog(false)}/>}
        <div>
            <div className="page-title">
                <MenuButton/>
                <h2>{t('user')}</h2>
            </div>
            <div className="buttons">
                {id && <>
                    {!is_current_user && <button type="button" className="delete" onClick={remove} disabled={!user?.actions?.edit_users}>
                        <IconTrash/>
                    </button>}
                    {!is_current_user && user?.role > data.role && <button type="button" onClick={impersonate}><IconUsers/></button>}
                    <button type="button" onClick={() => window.open(`/${settings.blog_url}/author/${data.slug}`, '_blank').focus()}><IconEye/></button>
                </>}
                <button type="submit" disabled={!is_current_user && !Boolean(user?.actions?.edit_users)}>{t('save')}</button>
            </div>
        </div>
        <div className="grid grid-two-columns wide">
            <div>
                <div className="user-image pointer" onClick={() => setOpenImageDialog(true)}>
                    <img src={data.image ? data.image : '/public/assets/no-image.svg'} className={!data.image ? 'empty-img' : ''}/>
                </div>
                {id && <div className="extra-info">
                    <p>ID: {id}</p>
                    <p>{t('no_posts')}: {data.posts}</p>
                    <p>{t('last_active')}: {formatDate(data.last_active)}</p>
                </div>}
            </div>
            <div className="grid">
                <div className="card v-spacing">
                    <div className="input-group">
                        <label htmlFor="name">{t('name')}</label>
                        <Input id="name" type="text" value={data.name} onChange={e => setData({ ...data, name: e.target.value })} charCount={true}/>
                    </div>
                    <div className="input-group">
                        <label htmlFor="slug">{t('slug')}</label>
                        <Input id="slug" type="text" value={data.slug} onChange={e => setData({ ...data, slug: getSlug(e.target.value) })} charCount={true}/>
                        <a href={getUrl(`/${settings.blog_url}/author/${data.slug}`)} target="_blank">{getUrl(`/${settings.blog_url}/author/${data.slug}`)}</a>
                    </div>
                    <div className="input-group">
                        <label htmlFor="email">{t('email')}</label>
                        <Input id="email" type="text" value={data.email} onChange={e => setData({ ...data, email: e.target.value })}/>
                    </div>
                    <div className="input-group">
                        <label htmlFor="bio">{t('bio')}</label>
                        <Textarea id="bio" value={data.bio} onChange={e => setData({ ...data, bio: e.target.value })} charCount={true}/>
                    </div>
                    <div className="input-group">
                        <label htmlFor="role">{t('role')}</label>
                        <select
                            id="role"
                            value={data.role ?? ''}
                            disabled={is_loading_roles}
                            aria-busy={is_loading_roles ? true : undefined}
                            onChange={e => setData({ ...data, role: parseInt(e.target.value, 10) })}
                        >
                            {roles.filter(role => !is_current_user || role.level <= (user?.role ?? 0)).map(role => <option key={role.level} value={role.level}>{getRoleTitle(role.slug)}</option>)}
                        </select>
                    </div>
                    <div className="input-group">
                        <label>{t('status')}</label>
                        <Switch checked={data.status == 1} onChange={e => setData({ ...data, status: e.target.checked ? 1 : 0 })} disabled={is_current_user}/>
                    </div>
                </div>
                <div className="card v-spacing">
                    <h3>{t('password')}</h3>
                    <div className="input-group">
                        <label htmlFor="password">{t('new_password')}</label>
                        <Input id="password" type="password" value={data.password || ''} onChange={e => setData({ ...data, password: e.target.value })}/>
                    </div>
                    <div className="input-group">
                        <label htmlFor="password-confirm">{t('password_confirm')}</label>
                        <Input id="password-confirm" type="password" value={data.password_confirm || ''} onChange={e => setData({ ...data, password_confirm: e.target.value })}/>
                    </div>
                </div>
                {sessions && is_current_user && <div className="card v-spacing">
                    <h3>{t('active_sessions')}</h3>
                    {sessions.sort((a, b) => b.updated_at - a.updated_at).map(session => <Session
                        key={session.id}
                        id={session.id}
                        userAgent={session.user_agent}
                        ip={session.ip}
                        createdAt={session.created_at}
                        updatedAt={session.updated_at}
                        current={session.current}
                        fetchSessions={fetch_sessions}
                        t={t}
                    />)}
                </div>}
            </div>
        </div>
    </form>);
}
