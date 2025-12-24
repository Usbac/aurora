import React, { useEffect, useState } from 'react';
import { Input, LoadingPage, makeRequest, MenuButton, Switch } from '../utils/utils';
import { IconEye, IconTrash } from '../utils/icons';
import { useLocation, useNavigate, useOutletContext } from 'react-router-dom';

export default function Link() {
    const { user } = useOutletContext();
    const [ data, setData ] = useState(undefined);
    const location = useLocation();
    const navigate = useNavigate();
    const params = new URLSearchParams(location.search);
    const [ id, setId ] = useState(params.get('id'));

    useEffect(() => {
        if (id) {
            makeRequest({
                method: 'GET',
                url: `/api/links?id=${id}`,
            }).then(res => setData(res?.data?.data[0] ?? null));
        } else {
            setData({});
        }
    }, []);

    const remove = () => {
        if (confirm('Are you sure you want to delete the link? This action cannot be undone.')) {
            makeRequest({
                method: 'DELETE',
                url: '/api/links',
                data: { id: id },
            }).then(res => {
                if (res?.data?.success) {
                    alert('Done');
                    navigate('/admin/links', { replace: true });
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
            url: '/api/links' + (id ? `?id=${id}` : ''),
            data: data,
        }).then(res => {
            alert(res?.data?.success ? 'Done' : 'Error');
            if (res?.data?.id) {
                navigate(`/admin/links/edit?id=${res.data.id}`, { replace: true });
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
                <h2>Link</h2>
            </div>
            <div class="buttons">
                {id && <>
                    <button type="button" class="delete" onClick={remove} disabled={!user?.actions?.edit_links}>
                        <IconTrash/>
                    </button>
                    <button type="button" onClick={() => window.open(data.url, '_blank').focus()}><IconEye/></button>
                </>}
                <button type="submit" disabled={!user?.actions?.edit_links}>Save</button>
            </div>
        </div>
       <div class="small-form">
            <div class="card v-spacing">
                <div class="input-group">
                    <label htmlFor="title">Title</label>
                    <Input
                        id="title"
                        type="text"
                        value={data.title}
                        onChange={e => setData({...data, title: e.target.value})}
                        charCount={true}
                    />
                </div>
                <div class="input-group">
                    <label htmlFor="url">URL</label>
                    <Input
                        id="url"
                        type="text"
                        value={data.url}
                        onChange={e => setData({...data, url: e.target.value})}
                        charCount={true}
                    />
                </div>
                <div class="input-group">
                    <label htmlFor="order">Order</label>
                    <Input
                        id="order"
                        type="number"
                        value={data.order}
                        onChange={e => setData({...data, order: e.target.value})}
                    />
                </div>
                <div class="input-group">
                    <label>Status</label>
                    <Switch checked={data.status == 1} onChange={e => setData({...data, status: e.target.checked })}/>
                </div>
                {id && <div class="extra-data">
                    <span>ID: {id}</span>
                </div>}
            </div>
        </div>
    </form>);
}