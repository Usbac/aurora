import React, { useState } from 'react';
import { makeRequest } from '../utils/utils';
import { useNavigate } from 'react-router-dom';
import { useI18n } from '../providers/I18nProvider';

export default function NewPassword() {
    const logo = document.querySelector('meta[name="logo"]')?.content;
    const [ loading, setLoading ] = useState(false);
    const [ password, setPassword ] = useState('');
    const [ password_confirm, setPasswordConfirm ] = useState('');
    const navigate = useNavigate();
    const { t } = useI18n();

    const submit = async e => {
        setLoading(true);
        e.preventDefault();
        makeRequest({
            method: 'POST',
            url: '/api/password-reset/confirm',
            data: {
                hash: (new URLSearchParams(window.location.search)).get('hash'),
                password: password,
                password_confirm: password_confirm,
            },
        }).then(res => {
            if (!res?.data?.success) {
                alert(t('invalid_email_or_password'));
            } else {
                navigate('/admin/dashboard');
            }
        }).finally(() => setLoading(false));
    };

    return <div className="login-page">
        <form className="card v-spacing" onSubmit={submit}>
            {logo && <img src={logo}/>}
            <div className="input-group">
                <label htmlFor="password">{t('new_password')}</label>
                <input id="password" type="password" value={password} onChange={e => setPassword(e.target.value)}/>
            </div>
            <div className="input-group">
                <label htmlFor="password-confirm">{t('password_confirm')}</label>
                <input id="password-confirm" type="password" value={password_confirm} onChange={e => setPasswordConfirm(e.target.value)}/>
            </div>
            <button type="submit" disabled={loading}>{t('reset_password')}</button>
        </form>
    </div>;
}