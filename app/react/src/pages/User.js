import React, { useEffect, useState } from 'react';
import { getContentUrl, getUrl, ImageDialog, Input, LoadingPage, makeRequest, MenuButton, Switch, Textarea, useRequest, formatDate, getRoleTitle } from '../utils/utils';
import { IconEye, IconTrash, IconUsers } from '../utils/icons';
import { useLocation, useNavigate, useOutletContext } from 'react-router-dom';

export default function User() {
	const { user, settings, fetch_user } = useOutletContext();
	const [ data, setData ] = useState(undefined);
	const [ open_image_dialog, setOpenImageDialog ] = useState(false);
	const { data: roles_req, is_loading: is_loading_roles, fetch: fetch_roles } = useRequest({
		method: 'GET',
		url: '/api/v2/roles',
	});
	const location = useLocation();
	const navigate = useNavigate();
	const params = new URLSearchParams(location.search);
	const [ id, setId ] = useState(params.get('id'));
	const roles = roles_req?.data ?? {};
	const is_current_user = id && user?.id == id;

	useEffect(() => {
		fetch_roles();

		if (id) {
			makeRequest({
				method: 'GET',
				url: `/api/v2/users?id=${id}`,
			}).then(res => setData(res?.data?.data[0] ?? null));
		} else {
			setData({});
		}
	}, []);

	const remove = () => {
		if (confirm('Are you sure you want to delete the user? This action cannot be undone.')) {
			makeRequest({
				method: 'DELETE',
				url: '/api/v2/users',
				data: { id: id },
			}).then(res => {
				if (res?.data?.success) {
					alert('Done');
					navigate('/console/users', { replace: true });
				} else {
					alert('Error');
				}
			});
		}
	};

	const impersonate = () => {
		if (confirm('Are you sure you want to impersonate this user?')) {
			makeRequest({
				method: 'GET',
				url: '/api/v2/users/impersonate?id=' + id,
			}).then(res => {
				if (!res?.data?.success) {
					alert('Error');
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
			url: '/api/v2/users' + (id ? `?id=${id}` : ''),
			data: data,
		}).then(res => {
			alert(res?.data?.success ? 'Done' : 'Error');
			if (res?.data?.id) {
				navigate(`/console/users/edit?id=${res.data.id}`, { replace: true });
				setId(res.data.id);
			}
		});
	};

	if (data === undefined || is_loading_roles) {
		return <LoadingPage/>;
	}

	if (!data) {
		return <>Error</>;
	}

	return (<form id="user-form" className="content" onSubmit={submit}>
		{open_image_dialog && <ImageDialog onSave={path => { setOpenImageDialog(false); setData({ ...data, image: path }); }} onClose={() => setOpenImageDialog(false)}/>}
		<div>
			<div class="page-title">
				<MenuButton/>
				<h2>User</h2>
			</div>
			<div class="buttons">
				{id && <>
					{!is_current_user && <button type="button" class="delete" onClick={remove} disabled={!user?.actions?.edit_users}>
						<IconTrash/>
					</button>}
					{!is_current_user && user?.role > data.role && <button type="button" onClick={impersonate}><IconUsers/></button>}
					<button type="button" onClick={() => window.open(`/${settings.blog_url}/author/${data.slug}`, '_blank').focus()}><IconEye/></button>
				</>}
				<button type="submit" disabled={!user?.actions?.edit_users}>Save</button>
			</div>
		</div>
		<div class="grid grid-two-columns wide">
			<div>
				<div class="user-image pointer" onClick={() => setOpenImageDialog(true)}>
					<img src={data.image ? getContentUrl(data.image) : '/public/assets/no-image.svg'} class={!data.image ? 'empty-img' : ''}/>
				</div>
				{id && <div class="extra-info">
					<p>ID: {id}</p>
					<p>No. posts: {data.posts}</p>
					<p>Last active: {formatDate(data.last_active)}</p>
				</div>}
			</div>
			<div class="grid">
				<div class="card v-spacing">
					<div class="input-group">
						<label htmlFor="name">Name</label>
						<Input id="name" type="text" value={data.name} onChange={e => setData({ ...data, name: e.target.value })} charCount={true}/>
					</div>
					<div class="input-group">
						<label htmlFor="slug">Slug</label>
						<Input id="slug" type="text" value={data.slug} onChange={e => setData({ ...data, slug: e.target.value })} charCount={true}/>
						<a href={getUrl(`/${settings.blog_url}/author/${data.slug}`)} target="_blank">{getUrl(`/${settings.blog_url}/author/${data.slug}`)}</a>
					</div>
					<div class="input-group">
						<label htmlFor="email">Email</label>
						<Input id="email" type="text" value={data.email} onChange={e => setData({ ...data, email: e.target.value })}/>
					</div>
					<div class="input-group">
						<label htmlFor="bio">Bio</label>
						<Textarea id="bio" value={data.bio} onChange={e => setData({ ...data, bio: e.target.value })} charCount={true}/>
					</div>
					<div class="input-group">
						<label htmlFor="role">Role</label>
						<select id="role" onChange={e => setData({ ...data, role: e.target.value })}>
							{Object.keys(roles).map(key => {
								const role = roles[key];
								return <option value={role.level} selected={data.role == role.level}>{getRoleTitle(role.slug)}</option>;
							})}
						</select>
					</div>
					<div class="input-group">
						<label>Status</label>
						<Switch checked={data.status == 1} onChange={e => setData({ ...data, status: e.target.checked ? 1 : 0 })} disabled={is_current_user}/>
					</div>
				</div>
				<div class="card v-spacing">
					<h3>Password</h3>
					<div class="input-group">
						<label htmlFor="password">New password</label>
						<Input id="password" type="password" value={data.password || ''} onChange={e => setData({ ...data, password: e.target.value })}/>
					</div>
					<div class="input-group">
						<label htmlFor="password-confirm">Password confirm</label>
						<Input id="password-confirm" type="password" value={data.password_confirm || ''} onChange={e => setData({ ...data, password_confirm: e.target.value })}/>
					</div>
				</div>
			</div>
		</div>
	</form>);
}


