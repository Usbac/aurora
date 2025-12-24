import React, { useEffect, useState } from 'react';
import { DateTimeInput, Editor, getContentUrl, getSlug, getUrl, ImageDialog, Input, LoadingPage, makeRequest, MenuButton, Switch, Textarea, useRequest } from '../utils/utils';
import { IconEye, IconTrash } from '../utils/icons';
import { useLocation, useNavigate, useOutletContext } from 'react-router-dom';

export default function Post() {
    const { user, settings, theme } = useOutletContext();
    const [ data, setData ] = useState(undefined);
    const [ open_image_dialog, setOpenImageDialog ] = useState(false);
    const { data: users_req, is_loading: is_loading_users, fetch: fetch_users } = useRequest({
        method: 'GET',
        url: '/api/users',
        data: {
            order: 'name',
            sort: 'asc',
        },
    });
    const { data: tags_req, is_loading: is_loading_tags, fetch: fetch_tags } = useRequest({
        method: 'GET',
        url: '/api/tags',
        data: {
            order: 'name',
            sort: 'asc',
        },
    });
    const location = useLocation();
    const navigate = useNavigate();
    const params = new URLSearchParams(location.search);
    const [ id, setId ] = useState(params.get('id'));
    const users = users_req?.data?.data ?? {};
    const tags = tags_req?.data?.data ?? [];

    useEffect(() => {
        fetch_users();
        fetch_tags();

        if (id) {
            makeRequest({
                method: 'GET',
                url: `/api/posts?id=${id}`,
            }).then(res => setData(res?.data?.data[0] ?? null));
        } else {
            setData({});
        }
    }, []);

    const remove = () => {
        if (confirm('Are you sure you want to delete the post? This action cannot be undone.')) {
            makeRequest({
                method: 'DELETE',
                url: '/api/posts',
                data: { id: id },
            }).then(res => {
                if (res?.data?.success) {
                    alert('Done');
                    navigate('/admin/posts', { replace: true });
                } else {
                    alert('Error');
                }
            });
        }
    };

    const submit = e => {
        e.preventDefault();
        makeRequest({
            method: 'POST',
            url: '/api/posts' + (id ? `?id=${id}` : ''),
            data: {
                ...data,
                tags: Object.keys(data.tags || {}).map(tag_slug => tags.find(tag => tag.slug == tag_slug)?.id),
            },
        }).then(res => {
            alert(res?.data?.success ? 'Done' : 'Error');
            if (res?.data?.id) {
                navigate(`/admin/posts/edit?id=${res.data.id}`, { replace: true });
                setId(res.data.id);
            }
        });
    };

    if (data === undefined || is_loading_users || is_loading_tags) {
        return <LoadingPage/>;
    }

    if (!data) {
        return <>Error</>;
    }

    return (<form className="content" onSubmit={submit}>
        <div>
            <div class="page-title">
                <MenuButton/>
                <h2>Page</h2>
            </div>
            <div class="buttons">
                {id && <>
                    <button type="button" class="delete" onClick={remove} disabled={!user?.actions?.edit_posts}>
                        <IconTrash/>
                    </button>
                    <button type="button" onClick={() => window.open(getUrl(data.slug), '_blank').focus()}><IconEye/></button>
                </>}
                <button type="submit" disabled={!user?.actions?.edit_posts}>Save</button>
            </div>
        </div>
        <div class="grid grid-two-columns">
            <div class="grid">
                <div class="card v-spacing">
                    <div class="input-group">
                        <label htmlFor="title">Title</label>
                        <Input id="title" type="text" value={data.title} onChange={e => setData({ ...data, title: e.target.value })} charCount={true}/>
                    </div>
                </div>
                <Editor value={data.html} setValue={content => setData(prev => ({ ...prev, html: content }))} theme={theme}/>
            </div>
            <div class="grid">
                <div class="card v-spacing">
                    <div class="input-group">
                        <label>Image</label>
                        <img src={data.image ? getContentUrl(data.image) : '/public/assets/no-image.svg'} class={`post-image pointer ${!data.image ? 'empty-img' : ''}`} alt="Post image" onClick={() => setOpenImageDialog(true)}/>
                        {open_image_dialog && <ImageDialog onSave={path => setData({ ...data, image: path })} onClose={() => setOpenImageDialog(false)}/>}
                    </div>
                    <div class="input-group">
                        <label htmlFor="slug">Slug</label>
                        <Input id="slug" type="text" placeholder="lorem-ipsum" value={data.slug} onChange={e => setData({ ...data, slug: getSlug(e.target.value) })} maxlength="255" charCount={true}/>
                        <a href={`/${settings.blog_url}/${data.slug}`} target="_blank">{getUrl(`/${settings.blog_url}/${data.slug}`)}</a>
                    </div>
                    <div class="input-group">
                        <label htmlFor="description">Description</label>
                        <Textarea id="description" charCount={true} value={data.description} onChange={e => setData({ ...data, description: e.target.value })}/>
                    </div>
                    {id && <div class="extra-data">
                        <span>ID: {id}</span>
                        <span>No. views: {data.views}</span>
                    </div>}
                </div>
                <div class="card v-spacing">
                    <div class="input-group">
                        <label htmlFor="published-at">Publish date</label>
                        <DateTimeInput id="published-at" value={data.published_at} onChange={value => setData({ ...data, published_at: value })}/>
                    </div>
                    <div class="input-group">
                        <label htmlFor="user-id">Author</label>
                        <select id="user-id" onChange={e => setData({ ...data, user_id: e.target.value })}>
                            <option value="">None</option>
                            {Object.values(users).map(user => <option value={user.id} selected={data.user_id == user.id}>{user.name}</option>)}
                        </select>
                    </div>
                    <div class="input-group">
                        <label htmlFor="published">Published</label>
                        <Switch id="published" checked={data.status == 1} onChange={e => setData({ ...data, status: e.target.checked ? 1 : 0 })}/>
                    </div>
                    {tags.length > 0 && <div className="input-group">
                        <label>Tags</label>
                        <div className="checkbox">
                            {tags.map(tag => <label>
                                <input
                                    type="checkbox"
                                    data-multiselect
                                    value={tag.id}
                                    checked={tag.slug in (data.tags || {})}
                                    onChange={e => {
                                        let new_tags = data.tags ? { ...data.tags } : {};
                                        if (e.target.checked) {
                                            new_tags[tag.slug] = tag.name;
                                        } else {
                                            delete new_tags[tag.slug];
                                        }

                                        setData({ ...data, tags: new_tags });
                                    }}
                                />
                                {tag.name}
                            </label>)}
                        </div>
                    </div>}
                </div>
                <div class="card v-spacing">
                    <div class="input-group">
                        <label htmlFor="image-alt">Image alt</label>
                        <Input id="image-alt" type="text" value={data.image_alt} onChange={e => setData({ ...data, image_alt: e.target.value })}/>
                    </div>
                    <div class="input-group">
                        <label htmlFor="meta-title">Meta title</label>
                        <Input id="meta-title" type="text" placeholder="lorem ipsum" value={data.meta_title} onChange={e => setData({ ...data, meta_title: e.target.value })} charCount={true}/>
                    </div>
                    <div class="input-group">
                        <label htmlFor="meta-description">Meta description</label>
                        <Textarea id="meta-description" value={data.meta_description} onChange={e => setData({ ...data, meta_description: e.target.value })} charCount={true}/>
                    </div>
                    <div class="input-group">
                        <label htmlFor="canonical-url">Canonical URL</label>
                        <Input id="canonical-url" type="text" placeholder={`/${settings.blog_url}/lorem-ipsum`} value={data.canonical_url} onChange={e => setData({ ...data, canonical_url: e.target.value })}/>
                    </div>
                </div>
            </div>
        </div>
    </form>);
}
