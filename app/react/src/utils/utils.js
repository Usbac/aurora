import React, { useRef } from 'react';
import axios from 'axios';
import { useQuery } from '@tanstack/react-query';

export const makeRequest = async ({ method = 'GET', url, data = null }) => {
    const form_data = new FormData();

    if (data) {
        Object.keys(data).forEach(key => form_data.append(key, data[key]));
    }

    return axios({
        method: method,
        url: url,
        data: form_data,
        headers: {
            Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
        },
    }).catch(err => {
        console.error(err);
    });
};

export const useRequest = ({ method = 'GET', url, data = null, options = {} }) => {
    return useQuery({
        queryKey: [ url, method, data, localStorage.getItem('auth_token') ],
        queryFn: () => makeRequest({ method, url, data }),
        staleTime: 5 * 60 * 1000, // 5 minutes
        refetchOnWindowFocus: true,
        ...options
    });
};

export const useElement = (url) => {
    const { data: data, isLoading: is_loading, isError: is_error } = useRequest({
        url: url,
        staleTime: 0,
    });

    if (is_loading) {
        return undefined;
    }

    return data?.data && !is_error ? data.data : null;
};

export const MenuButton = () => <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-list pointer" viewBox="0 0 16 16" onClick={() => document.body.toggleAttribute('data-nav-open')}>
    <path fillRule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
</svg>;

export const LoadingPage = () => <div className="content">
    <div className="loading-page">
        <div className="spinner"/>
    </div>
</div>;

export const Input = (props) => {
    const char_count = props.value?.length || 0;

    return <>
        <input {...props}/>
        {props.charCount && <span class="char-counter">{char_count} character{char_count !== 1 ? 's' : ''}</span>}
    </>;
};

export const Switch = (props) => {
    const ref = useRef(null);

    return <div class="switch">
        <input ref={ref} type="checkbox" {...props}/>
        <button type="button" class="slider" onClick={() => ref.current.click()}></button>
    </div>;
};