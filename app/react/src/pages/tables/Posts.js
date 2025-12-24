import React, { useEffect, useMemo } from 'react';
import { Table } from '../../components/Table';
import { useNavigate, useOutletContext } from 'react-router-dom';
import { DropdownMenu, formatDate, getContentUrl, LoadingPage, makeRequest, useRequest } from '../../utils/utils';
import { IconEye, IconThreeDots, IconTrash } from '../../utils/icons';

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
    const users_options = useMemo(() => {
        let users = users_req?.data?.data ?? {};

        return [
            { key: '', title: 'All' },
            ...Object.keys(users).map(key => ({ key: users[key].id, title: users[key].name })),
        ];
    }, [ users_req ]);

    useEffect(() => {
        fetch_users();
    }, []);

    if (is_loading_users) {
        return <LoadingPage/>;
    }

    return <div className="content">
        <Table
            url="/api/posts"
            title="Posts"
            topOptions={[
                {
                    content: <><b>+</b>&nbsp;New</>,
                    condition: Boolean(user?.actions?.edit_posts),
                    onClick: () => navigate('/admin/posts/edit'),
                },
            ]}
            rowOnClick={post => navigate(`/admin/posts/edit?id=${post.id}`)}
            filters={{
                user: {
                    title: 'Author',
                    options: users_options,
                },
                status: {
                    title: 'Status',
                    options: [
                        { key: '', title: 'All' },
                        { key: '1', title: 'Published' },
                        { key: 'scheduled', title: 'Scheduled' },
                        { key: '0', title: 'Draft' },
                    ],
                },
                order: {
                    title: 'Sort by',
                    options: [
                        { key: 'title', title: 'Title' },
                        { key: 'author', title: 'Author' },
                        { key: 'date', title: 'Publish Date' },
                        { key: 'views', title: 'No. views' },
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
                    condition: Boolean(user?.actions?.edit_posts),
                    onClick: (posts) => {
                        if (confirm('Are you sure you want to delete the selected posts? This action cannot be undone.')) {
                            makeRequest({
                                method: 'DELETE',
                                url: '/api/posts',
                                data: { id: posts.map(l => l.id) },
                            }).then(res => alert(res?.data?.success ? 'Done' : 'Error'));
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
                                {!post.status && <span className="title-label red">Draft</span>}
                                {post.status && post.published_at > Date.now() / 1000 && <span className="title-label">Scheduled</span>}
                            </h3>
                            <p className="subtitle">{Object.values(post.tags)?.join(', ') || ''}</p>
                        </div>
                    </>,
                },
                {
                    title: 'Author',
                    class: 'w20',
                    content: post => post.user_name || '',
                },
                {
                    title: 'Publish Date',
                    class: 'w20',
                    content: post => formatDate(post.published_at),
                },
                {
                    title: 'No. views',
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
                                content: <><IconEye/> View</>
                            },
                            {
                                class: 'danger',
                                condition: Boolean(user?.actions?.edit_posts),
                                onClick: () => {
                                    if (confirm('Are you sure you want to delete the post? This action cannot be undone.')) {
                                        makeRequest({
                                            method: 'DELETE',
                                            url: '/api/posts',
                                            data: { id: post.id },
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
