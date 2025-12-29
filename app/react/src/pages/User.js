import React, { useEffect, useState } from 'react';
import { getContentUrl, getUrl, ImageDialog, Input, LoadingPage, makeRequest, MenuButton, Switch, Textarea, useRequest, formatDate, getRoleTitle, getSlug } from '../utils/utils';
import { IconEye, IconTrash, IconUsers } from '../utils/icons';
import { useLocation, useNavigate, useOutletContext } from 'react-router-dom';
import { useI18n } from '../providers/I18nProvider';

export default function User() {
	const { user, settings, fetch_user } = useOutletContext();
	const [ data, setData ] = useState(undefined);
	const [ open_image_dialog, setOpenImageDialog ] = useState(false);
	const { data: roles_req, is_loading: is_loading_roles, fetch: fetch_roles } = useRequest({
		method: 'GET',
		url: '/api/roles',
	});
	const location = useLocation();
	const navigate = useNavigate();
	const params = new URLSearchParams(location.search);
	const [ id, setId ] = useState(params.get('id'));
	const roles = roles_req?.data ?? {};
	const is_current_user = id && user?.id == id;
	const { t } = useI18n();

	useEffect(() => {
		fetch_roles();

		if (id) {
			makeRequest({
				method: 'GET',
				url: `/api/users?id=${id}`,
			}).then(res => setData(res?.data?.data[0] ?? null));
		} else {
			setData({});
		}
	}, []);

	const remove = () => {
		if (confirm(t('confirm_delete_user', data.name))) {
			makeRequest({
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
			makeRequest({
				method: 'GET',
				url: '/api/users/impersonate?id=' + id,
			}).then(res => {
				if (!res?.data?.success) {
					alert(t('error_impersonating_user'));
				} else {
					localStorage.setItem('auth_token', res.data.token);
					fetch_user();
				}
			});
		}
	};

	const submit = e => {
		e.preventDefault();
		makeRequest({
			method: 'POST',
			url: '/api/users' + (id ? `?id=${id}` : ''),
			data: data,
		}).then(res => {
			alert(res?.data?.success ? t('user_saved_successfully') : t('error_saving_user'));
			if (res?.data?.id) {
				navigate(`/admin/users/edit?id=${res.data.id}`, { replace: true });
				setId(res.data.id);
			}
		});
	};

	if (data === undefined || is_loading_roles) {
		return <LoadingPage/>;
	}

	if (!data) {
		return <>{t('error')}</>;
	}

	return (<form id="user-form" className="content" onSubmit={submit}>
		{open_image_dialog && <ImageDialog onSave={path => { setOpenImageDialog(false); setData({ ...data, image: path }); }} onClose={() => setOpenImageDialog(false)}/>}
		<div>
			<div class="page-title">
				<MenuButton/>
				<h2>{t('user')}</h2>
			</div>
			<div class="buttons">
				{id && <>
					{!is_current_user && <button type="button" class="delete" onClick={remove} disabled={!user?.actions?.edit_users}>
						<IconTrash/>
					</button>}
					{!is_current_user && user?.role > data.role && <button type="button" onClick={impersonate}><IconUsers/></button>}
					<button type="button" onClick={() => window.open(`/${settings.blog_url}/author/${data.slug}`, '_blank').focus()}><IconEye/></button>
				</>}
				<button type="submit" disabled={!user?.actions?.edit_users}>{t('save')}</button>
			</div>
		</div>
		<div class="grid grid-two-columns wide">
			<div>
				<div class="user-image pointer" onClick={() => setOpenImageDialog(true)}>
					<img src={data.image ? getContentUrl(data.image) : '/public/assets/no-image.svg'} class={!data.image ? 'empty-img' : ''}/>
				</div>
				{id && <div class="extra-info">
					<p>ID: {id}</p>
					<p>{t('no_posts')}: {data.posts}</p>
					<p>{t('last_active')}: {formatDate(data.last_active)}</p>
				</div>}
			</div>
			<div class="grid">
				<div class="card v-spacing">
					<div class="input-group">
						<label htmlFor="name">{t('name')}</label>
						<Input id="name" type="text" value={data.name} onChange={e => setData({ ...data, name: e.target.value })} charCount={true}/>
					</div>
					<div class="input-group">
						<label htmlFor="slug">{t('slug')}</label>
						<Input id="slug" type="text" value={data.slug} onChange={e => setData({ ...data, slug: getSlug(e.target.value) })} charCount={true}/>
						<a href={getUrl(`/${settings.blog_url}/author/${data.slug}`)} target="_blank">{getUrl(`/${settings.blog_url}/author/${data.slug}`)}</a>
					</div>
					<div class="input-group">
						<label htmlFor="email">{t('email')}</label>
						<Input id="email" type="text" value={data.email} onChange={e => setData({ ...data, email: e.target.value })}/>
					</div>
					<div class="input-group">
						<label htmlFor="bio">{t('bio')}</label>
						<Textarea id="bio" value={data.bio} onChange={e => setData({ ...data, bio: e.target.value })} charCount={true}/>
					</div>
					<div class="input-group">
						<label htmlFor="role">{t('role')}</label>
						<select id="role" onChange={e => setData({ ...data, role: e.target.value })}>
							{Object.keys(roles).map(key => {
								const role = roles[key];
								return <option value={role.level} selected={data.role == role.level}>{getRoleTitle(role.slug)}</option>;
							})}
						</select>
					</div>
					<div class="input-group">
						<label>{t('status')}</label>
						<Switch checked={data.status == 1} onChange={e => setData({ ...data, status: e.target.checked ? 1 : 0 })} disabled={is_current_user}/>
					</div>
				</div>
				<div class="card v-spacing">
					<h3>{t('password')}</h3>
					<div class="input-group">
						<label htmlFor="password">{t('new_password')}</label>
						<Input id="password" type="password" value={data.password || ''} onChange={e => setData({ ...data, password: e.target.value })}/>
					</div>
					<div class="input-group">
						<label htmlFor="password-confirm">{t('password_confirm')}</label>
						<Input id="password-confirm" type="password" value={data.password_confirm || ''} onChange={e => setData({ ...data, password_confirm: e.target.value })}/>
					</div>
				</div>
			</div>
		</div>
	</form>);
}
