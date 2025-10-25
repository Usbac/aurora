import React, { useEffect, useState } from 'react';
import { Editor, getUrl, Input, LoadingPage, makeRequest, MenuButton, Switch, Textarea, useRequest } from '../utils/utils';
import { IconEye, IconTrash } from '../utils/icons';
import { useLocation, useNavigate, useOutletContext } from 'react-router-dom';

export default function Page() {
    const { user, theme } = useOutletContext();
    const [ data, setData ] = useState(undefined);
    const { data: view_files_req, is_loading: is_loading_view_files, fetch: fetch_view_files } = useRequest({
        method: 'GET',
        url: '/api/v2/view_files',
    });
    const location = useLocation();
    const navigate = useNavigate();
    const params = new URLSearchParams(location.search);
    const [ id, setId ] = useState(params.get('id'));
    const view_files = view_files_req?.data ?? [];

    useEffect(() => {
        fetch_view_files();

        if (id) {
            makeRequest({
                method: 'GET',
                url: `/api/v2/pages?id=${id}`,
            }).then(res => {
                const pageData = res?.data?.data[0] ?? null;
                console.log('Page data from API:', pageData);
                setData(pageData);
            });
        } else {
            setData({});
        }
    }, []);

    const remove = () => {
        if (confirm('Are you sure you want to delete the page? This action cannot be undone.')) {
            makeRequest({
                method: 'DELETE',
                url: '/api/v2/pages',
                data: { id: id },
            }).then(res => {
                if (res?.data?.success) {
                    alert('Done');
                    navigate('/console/pages', { replace: true });
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
            url: '/api/v2/pages' + (id ? `?id=${id}` : ''),
            data: data,
        }).then(res => {
            alert(res?.data?.success ? 'Done' : 'Error');
            if (res?.data?.id) {
                navigate(`/console/pages/edit?id=${res.data.id}`, { replace: true });
                setId(res.data.id);
            }
        });
    };

    if (data === undefined || is_loading_view_files) {
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
                    <button type="button" class="delete" onClick={remove} disabled={!user?.actions?.edit_pages}>
                        <IconTrash/>
                    </button>
                    <button type="button" onClick={() => window.open(getUrl(data.slug), '_blank').focus()}><IconEye/></button>
                </>}
                <button type="submit" disabled={!user?.actions?.edit_pages}>Save</button>
            </div>
        </div>
        <div class="grid grid-two-columns">
            <div class="grid">
                <div class="card v-spacing">
                    <div class="input-group">
                        <label htmlFor="title">Title</label>
                        <Input id="title" type="text" value={data.title} onChange={e => setData({...data, title: e.target.value})} charCount={true}/>
                    </div>
                </div>
                <div id="page-editor" style={{ display: data.static ? 'none' : 'flex' }}>
                    <Editor key={theme} value={data.html} setValue={content => setData(prev => ({ ...prev, html: content }))} theme={theme}/>
                </div>
            </div>
            <div class="grid">
                <div class="card v-spacing">
                    <div class="input-group">
                        <label htmlFor="slug">Slug</label>
                        <Input id="slug" type="text" placeholder="lorem-ipsum" value={data.slug} onChange={e => setData({...data, slug: e.target.value})} maxLength="255" charCount={true}/>
                        <a href={getUrl(data.slug)} target="_blank">{getUrl(data.slug)}</a>
                    </div>
                    {id && <div class="extra-data">
                        <span>ID: {id}</span>
                        <span>No. views: {data.views}</span>
                    </div>}
                </div>
                <div class="card v-spacing">
                    <div class="input-group">
                        <label htmlFor="status">Published</label>
                        <Switch checked={data.status == 1} onChange={e => setData({ ...data, status: e.target.checked ? 1 : 0 })}/>
                    </div>
                    <div class="input-group">
                        <label htmlFor="static">Static</label>
                        <Switch checked={data.static == 1} onChange={e => setData({ ...data, static: e.target.checked ? 1 : 0 })}/>
                    </div>
                    <div class="input-group">
                        <label htmlFor="static-file">Static file</label>
                        <select id="static-file" onChange={e => setData({ ...data, static_file: e.target.value })}>
                            <option value="">None</option>
                            {view_files.map(file => <option key={file} value={file} selected={file === data.static_file}>{file}</option>)}
                        </select>
                    </div>
                </div>
                <div class="card v-spacing">
                    <div class="input-group">
                        <label htmlFor="meta-title">Meta title</label>
                        <Input id="meta-title" type="text" placeholder="lorem ipsum" value={data.meta_title} onChange={e => setData({...data, meta_title: e.target.value})} charCount={true}/>
                    </div>
                    <div class="input-group">
                        <label htmlFor="meta-description">Meta description</label>
                        <Textarea id="meta-description" charCount={true} value={data.meta_description} onChange={e => setData({...data, meta_description: e.target.value})}/>
                    </div>
                    <div class="input-group">
                        <label htmlFor="canonical-url">Canonical URL</label>
                        <Input id="canonical-url" type="text" placeholder={getUrl('/about')} value={data.canonical_url} onChange={e => setData({...data, canonical_url: e.target.value})}/>
                    </div>
                </div>
            </div>
        </div>
    </form>);
}
