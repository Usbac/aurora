import React, { useEffect, useMemo, useRef } from 'react';
import { Table } from '../../utils/Table';
import { useNavigate, useOutletContext } from 'react-router-dom';
import { DropdownMenu, formatDate, getContentUrl, LoadingPage, makeRequest, useRequest } from '../../utils/utils';
import { IconEye, IconThreeDots, IconTrash } from '../../utils/icons';
import { useI18n } from '../../providers/I18nProvider';

export default function Posts() {
    const { user, settings } = useOutletContext();
    const { data: users_req, is_loading: is_loading_users, fetch: fetch_users } = useRequest({
        method: 'GET',
        url: '/api/users',
        data: {
            order: 'name',
            sort: 'asc',
        },
    });
    const navigate = useNavigate();
    const { t } = useI18n();
    const table_ref = useRef(null);
    const users_options = useMemo(() => {
        let users = users_req?.data?.data ?? {};

        return [
            { key: '', title: t('all') },
            ...Object.keys(users).map(key => ({ key: users[key].id, title: users[key].name })),
        ];
    }, [ users_req, t ]);

    useEffect(() => {
        fetch_users();
    }, []);

    if (is_loading_users) {
        return <LoadingPage/>;
    }

    return <div className="content">
        <Table
            ref={table_ref}
            url="/api/posts"
            title={t('posts')}
            topOptions={[
                {
                    content: <><b>+</b>&nbsp;{t('new')}</>,
                    condition: Boolean(user?.actions?.edit_posts),
                    onClick: () => navigate('/admin/posts/edit'),
                },
            ]}
            rowOnClick={post => navigate(`/admin/posts/edit?id=${post.id}`)}
            filters={{
                user: {
                    title: t('author'),
                    options: users_options,
                },
                status: {
                    title: t('status'),
                    options: [
                        { key: '', title: t('all') },
                        { key: '1', title: t('published') },
                        { key: 'scheduled', title: t('scheduled') },
                        { key: '0', title: t('draft') },
                    ],
                },
                order: {
                    title: t('sort_by'),
                    options: [
                        { key: 'title', title: t('title') },
                        { key: 'author', title: t('author') },
                        { key: 'date', title: t('publish_date') },
                        { key: 'views', title: t('no_views') },
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
                    condition: Boolean(user?.actions?.edit_posts),
                    onClick: (posts) => {
                        if (confirm(t('confirm_delete_selected_posts'))) {
                            makeRequest({
                                method: 'DELETE',
                                url: '/api/posts',
                                data: { id: posts.map(l => l.id) },
                            }).then(res => {
                                alert(t(res?.data?.success ? 'posts_deleted_successfully' : 'error_deleting_posts'));
                                if (res?.data?.success) {
                                    table_ref?.current?.refetch();
                                }
                            });
                        }
                    },
                },
            ]}
            columns={[
                {
                    class: 'w100 align-center',
                    content: post => <>
                        <img
                            src={post.image ? getContentUrl(post.image) : ''}
                            alt={post.image_alt || ''}
                            className="row-thumb"
                            style={{ visibility: post.image ? 'visible' : 'hidden' }}
                        />
                        <div>
                            <h3>
                                {post.title}
                                {!post.status && <span className="title-label red">{t('draft')}</span>}
                                {post.status && post.published_at > Date.now() / 1000 && <span className="title-label">{t('scheduled')}</span>}
                            </h3>
                            <p className="subtitle">{Object.values(post.tags)?.join(', ') || ''}</p>
                        </div>
                    </>,
                },
                {
                    title: t('author'),
                    class: 'w20',
                    content: post => post.user_name || '',
                },
                {
                    title: t('publish_date'),
                    class: 'w20',
                    content: post => formatDate(post.published_at),
                },
                {
                    title: t('no_views'),
                    class: 'w10 numeric',
                    condition: Boolean(settings.views_count),
                    content: post => post.views || '',
                },
                {
                    class: 'w10 row-actions',
                    content: post => <DropdownMenu
                        content={<IconThreeDots/>}
                        className="three-dots"
                        options={[
                            {
                                onClick: () => window.open(`/${settings.blog_url}/${post.slug}`, '_blank').focus(),
                                content: <><IconEye/> {t('view')}</>
                            },
                            {
                                class: 'danger',
                                condition: Boolean(user?.actions?.edit_posts),
                                onClick: () => {
                                    if (confirm(t('confirm_delete_post'))) {
                                        makeRequest({
                                            method: 'DELETE',
                                            url: '/api/posts',
                                            data: { id: post.id },
                                        }).then(res => {
                                            alert(t(res?.data?.success ? 'post_deleted_successfully' : 'error_deleting_post'));
                                            if (res?.data?.success) {
                                                table_ref?.current?.refetch();
                                            }
                                        });
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
