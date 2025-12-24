import React, { useEffect, useState } from 'react';
import { getSlug, Input, LoadingPage, makeRequest, MenuButton, Textarea } from '../utils/utils';
import { IconEye, IconTrash } from '../utils/icons';
import { useLocation, useNavigate, useOutletContext } from 'react-router-dom';

export default function Tag() {
    const { user, settings } = useOutletContext();
    const [ data, setData ] = useState(undefined);
    const location = useLocation();
    const navigate = useNavigate();
    const params = new URLSearchParams(location.search);
    const [ id, setId ] = useState(params.get('id'));

    useEffect(() => {
        if (id) {
            makeRequest({
                method: 'GET',
                url: `/api/tags?id=${id}`,
            }).then(res => setData(res?.data?.data[0] ?? null));
        } else {
            setData({});
        }
    }, []);

    const remove = () => {
        if (confirm('Are you sure you want to delete the tag? This action cannot be undone.')) {
            makeRequest({
                method: 'DELETE',
                url: '/api/tags',
                data: { id: id },
            }).then(res => {
                if (res?.data?.success) {
                    alert('Done');
                    navigate('/admin/tags', { replace: true });
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
            url: '/api/tags' + (id ? `?id=${id}` : ''),
            data: data,
        }).then(res => {
            alert(res?.data?.success ? 'Done' : 'Error');
            if (res?.data?.id) {
                navigate(`/admin/tags/edit?id=${res.data.id}`, { replace: true });
                setId(res.data.id);
            }
        });
    };

    if (data === undefined) {
        return <LoadingPage/>;
    }

    if (!data) {
        return <>Error</>;
    }

    return (<form className="content" onSubmit={submit}>
        <div>
            <div class="page-title">
                <MenuButton/>
                <h2>Tag</h2>
            </div>
            <div class="buttons">
                {id && <>
                    <button type="button" class="delete" onClick={remove} disabled={!user?.actions?.edit_tags}>
                        <IconTrash/>
                    </button>
                    <button type="button" onClick={() => window.open(`/${settings.blog_url}/tag/${data.slug}`, '_blank').focus()}><IconEye/></button>
                </>}
                <button type="submit" disabled={!user?.actions?.edit_tags}>Save</button>
            </div>
        </div>
       <div class="grid small-form">
            <div class="card v-spacing">
                <div class="input-group">
                    <label htmlFor="name">Name</label>
                    <Input
                        id="name"
                        type="text"
                        value={data.name}
                        onChange={e => setData({ ...data, name: e.target.value })}
                        charCount={true}
                    />
                </div>
                <div class="input-group">
                    <label htmlFor="slug">Slug</label>
                    <Input
                        id="slug"
                        type="text"
                        value={data.slug}
                        onChange={e => setData({ ...data, slug: getSlug(e.target.value) })}
                        charCount={true}
                    />
                </div>
                <div class="input-group">
                    <label htmlFor="description">Description</label>
                    <Textarea
                        id="description"
                        value={data.description}
                        onChange={e => setData({ ...data, description: e.target.value })}
                        charCount={true}
                    />
                </div>
                {id && <div class="extra-data">
                    <span>ID: {id}</span>
                    <span>No. posts: {data.posts}</span>
                </div>}
            </div>
            <div class="card v-spacing">
                <div class="input-group">
                    <label htmlFor="meta_title">Meta title</label>
                    <Input
                        id="meta_title"
                        value={data.meta_title}
                        onChange={e => setData({...data, meta_title: e.target.value})}
                        charCount={true}
                    />
                </div>
                <div class="input-group">
                    <label htmlFor="meta_description">Meta description</label>
                    <Textarea
                        id="meta_description"
                        value={data.meta_description}
                        onChange={e => setData({...data, meta_description: e.target.value})}
                        charCount={true}
                    />
                </div>
            </div>
        </div>
    </form>);
}