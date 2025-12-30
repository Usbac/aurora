import React, { useEffect, useMemo } from 'react';
import { Table } from '../../components/Table';
import { useNavigate, useOutletContext } from 'react-router-dom';
import { DropdownMenu, formatDate, getContentUrl, getRoleTitle, LoadingPage, makeRequest, useRequest } from '../../utils/utils';
import { IconEye, IconThreeDots, IconTrash, IconUsers } from '../../utils/icons';
import { useI18n } from '../../providers/I18nProvider';

export default function Users() {
    const { user, settings, fetch_user } = useOutletContext();
    const navigate = useNavigate();
    const { t } = useI18n();
    const { data: roles_req, is_loading: is_loading_roles, fetch: fetch_roles } = useRequest({
        method: 'GET',
        url: '/api/roles',
    });
    const roles_options = useMemo(() => {
        let roles = roles_req?.data ?? {};

        return [
            { key: '', title: t('all') },
            ...Object.keys(roles).map(key => ({ key: roles[key].level, title: getRoleTitle(roles[key].slug) })),
        ];
    }, [ roles_req, t ]);

    useEffect(() => {
        fetch_roles();
    }, []);

    if (is_loading_roles) {
        return <LoadingPage/>;
    }

    return <div class="content">
        <Table
            url="/api/users"
            title={t('users')}
            topOptions={[
                {
                    content: <><b>+</b>&nbsp;{t('new')}</>,
                    condition: Boolean(user?.actions?.edit_users),
                    onClick: () => navigate('/admin/users/edit'),
                },
            ]}
            rowOnClick={item => navigate(`/admin/users/edit?id=${item.id}`)}
            filters={{
                status: {
                    title: t('status'),
                    options: [
                        { key: '', title: t('all') },
                        { key: '1', title: t('active') },
                        { key: '0', title: t('inactive') },
                    ],
                },
                role: {
                    title: t('role'),
                    options: roles_options,
                },
                order: {
                    title: t('sort_by'),
                    options: [
                        { key: 'name', title: t('name') },
                        { key: 'email', title: t('email') },
                        { key: 'status', title: t('status') },
                        { key: 'role', title: t('role') },
                        { key: 'last_active', title: t('last_active') },
                        { key: 'posts', title: t('no_posts') },
                    ],
                },
                sort: {
                    options: [
                        { key: 'asc', title: t('ascending') },
                        { key: 'desc', title: t('descending') },
                    ],
                },
            }}
            options={[
                {
                    title: t('delete'),
                    class: 'danger',
                    condition: Boolean(user?.actions?.edit_users),
                    onClick: (users) => {
                        if (confirm(t('confirm_delete_selected_users'))) {
                            makeRequest({
                                method: 'DELETE',
                                url: '/api/users',
                                data: { id: users.map(u => u.id) },
                            }).then(res => alert(t(res?.data?.success ? 'users_deleted_successfully' : 'error_deleting_users')));
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
                                {item.id == user?.id && <span class="you-tag">{t('you')}</span>}
                                {item.status != 1 && <span class="title-label red">{t('inactive')}</span>}
                            </h3>
                            <p class="subtitle">{item.email}</p>
                        </div>
                    </>),
                },
                {
                    title: t('role'),
                    class: 'w20',
                    content: item => getRoleTitle(item.role_slug),
                },
                {
                    title: t('last_active'),
                    class: 'w20',
                    content: item => formatDate(item.last_active),
                },
                {
                    title: t('no_posts'),
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
                                content: <><IconEye/> {t('view')}</>
                            },
                            {
                                condition: item.id != user?.id && user.role > item.role,
                                onClick: () => {
                                    if (confirm(t('confirm_impersonate_user'))) {
                                        makeRequest({
                                            method: 'GET',
                                            url: '/api/users/impersonate?id=' + item.id,
                                        }).then(res => {
                                            if (!res?.data?.success) {
                                                alert(t('error_impersonating_user'));
                                            } else {
                                                fetch_user();
                                            }
                                        });
                                    }
                                },
                                content: <><IconUsers/> {t('impersonate')}</>
                            },
                            {
                                class: 'danger',
                                condition: item.id != user?.id && Boolean(user?.actions?.edit_users),
                                onClick: () => {
                                    if (confirm(t('confirm_delete_user', item.name))) {
                                        makeRequest({
                                            method: 'DELETE',
                                            url: '/api/users',
                                            data: { id: item.id },
                                        }).then(res => alert(t(res?.data?.success ? 'user_deleted_successfully' : 'error_deleting_user')));
                                    }
                                },
                                content: <><IconTrash/> {t('delete')}</>
                            },
                        ]}
                    />,
                },
            ]}
        />
    </div>
}
