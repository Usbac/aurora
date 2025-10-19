import React, { useEffect, useMemo } from 'react';
import { Table } from '../../components/Table';
import { useNavigate, useOutletContext } from 'react-router-dom';
import { DropdownMenu, formatDate, getContentUrl, getRoleTitle, LoadingPage, makeRequest, useRequest } from '../../utils/utils';
import { IconEye, IconThreeDots, IconTrash, IconUsers } from '../../utils/icons';

export default function Users() {
    const { user, settings } = useOutletContext();
    const navigate = useNavigate();
    const { data: roles_req, is_loading: is_loading_roles, fetch: fetch_roles } = useRequest({
        method: 'GET',
        url: '/api/v2/roles',
    });
    const roles_options = useMemo(() => {
        let roles = roles_req?.data ?? {};

        return [
            { key: '', title: 'All' },
            ...Object.keys(roles).map(key => ({ key: roles[key].level, title: getRoleTitle(roles[key].slug) })),
        ];
    }, [ roles_req ]);

    useEffect(() => {
        fetch_roles();
    }, []);

    if (is_loading_roles) {
        return <LoadingPage/>;
    }

    return <div class="content">
        <Table
            url="/api/v2/users"
            title="Users"
            topOptions={[
                {
                    content: <><b>+</b>&nbsp;New</>,
                    condition: Boolean(user?.actions?.edit_users),
                    onClick: () => navigate('/console/users/edit'),
                },
            ]}
            rowOnClick={item => navigate(`/console/users/edit?id=${item.id}`)}
            filters={{
                status: {
                    title: 'Status',
                    options: [
                        { key: '', title: 'All' },
                        { key: '1', title: 'Active' },
                        { key: '0', title: 'Inactive' },
                    ],
                },
                role: {
                    title: 'Role',
                    options: roles_options,
                },
                order: {
                    title: 'Sort by',
                    options: [
                        { key: 'name', title: 'Name' },
                        { key: 'email', title: 'Email' },
                        { key: 'status', title: 'Status' },
                        { key: 'role', title: 'Role' },
                        { key: 'last_active', title: 'Last Active' },
                        { key: 'posts', title: 'No. posts' },
                    ],
                },
                sort: {
                    options: [
                        { key: 'asc', title: 'Ascending' },
                        { key: 'desc', title: 'Descending' },
                    ],
                },
            }}
            options={[
                {
                    title: 'Delete',
                    class: 'danger',
                    condition: Boolean(user?.actions?.edit_users),
                    onClick: (users) => {
                        if (confirm('Are you sure you want to delete the selected users? This action cannot be undone.')) {
                            makeRequest({
                                method: 'DELETE',
                                url: '/api/v2/users',
                                data: { id: users.map(u => u.id) },
                            }).then(res => alert(res?.data?.success ? 'Done' : 'Error'));
                        }
                    },
                },
            ]}
            columns={[
                {
                    class: 'w100 align-center',
                    content: item => (<>
                        <div class="user-image">
                            <img
                                src={item.image ? getContentUrl(item.image) : '/assets/no-image.svg'}
                                className={item.image ? '' : 'empty-img'}
                                alt={item.name}
                                style={{ visibility: item.image ? 'visible' : 'hidden' }}
                            />
                        </div>
                        <div>
                            <h3>
                                {item.name}
                                {item.id == user?.id && <span class="you-tag">(you)</span>}
                                {item.status != 1 && <span class="title-label red">Inactive</span>}
                            </h3>
                            <p class="subtitle">{item.email}</p>
                        </div>
                    </>),
                },
                {
                    title: 'Role',
                    class: 'w20',
                    content: item => getRoleTitle(item.role_slug),
                },
                {
                    title: 'Last Active',
                    class: 'w20',
                    content: item => formatDate(item.last_active),
                },
                {
                    title: 'No. posts',
                    class: 'w10 numeric',
                    content: item => item.posts,
                },
                {
                    class: 'w10 row-actions',
                    content: item => <DropdownMenu
                        content={<IconThreeDots/>}
                        className="three-dots"
                        options={[
                            {
                                onClick: () => window.open(`/${settings.blog_url}/author/${item.slug}`, '_blank').focus(),
                                content: <><IconEye/> View</>
                            },
                            {
                                condition: item.id != user?.id && user.role > item.role,
                                onClick: () => {
                                    if (confirm('Are you sure you want to impersonate this user?')) {
                                        window.location.href = `/admin/users/impersonate?id=${item.id}`;
                                    }
                                },
                                content: <><IconUsers/> Impersonate</>
                            },
                            {
                                class: 'danger',
                                condition: item.id != user?.id && Boolean(user?.actions?.edit_users),
                                onClick: () => {
                                    if (confirm(`Are you sure you want to delete ${item.name}? This action cannot be undone.`)) {
                                        makeRequest({
                                            method: 'DELETE',
                                            url: '/api/v2/users',
                                            data: { id: item.id },
                                        }).then(res => alert(res?.data?.success ? 'Done' : 'Error'));
                                    }
                                },
                                content: <><IconTrash/> Delete</>
                            },
                        ]}
                    />,
                },
            ]}
        />
    </div>
}
