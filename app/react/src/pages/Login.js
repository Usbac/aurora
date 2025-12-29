import React, { useState } from 'react';
import { makeRequest, useElement } from '../utils/utils';
import { useNavigate } from 'react-router-dom';
import { useI18n } from '../providers/I18nProvider';

export default function Login() {
    const [ user ] = useElement('/api/me');
    const logo = document.querySelector('meta[name="logo"]')?.content;
    const [ loading, setLoading ] = useState(false);
    const [ email, setEmail ] = useState('');
    const [ password, setPassword ] = useState('');
    const [ reset_password, setResetPassword ] = useState(false);
    const navigate = useNavigate();
    const { t } = useI18n();

    const submitLogin = async e => {
        setLoading(true);
        e.preventDefault();
        makeRequest({
            method: 'POST',
            url: '/api/auth',
            data: {
                email: email,
                password: password,
            },
        }).then(res => {
            if (!res?.data?.success) {
                alert(t('invalid_email_or_password'));
            } else {
                localStorage.setItem('auth_token', res.data.token);
                navigate('/admin/dashboard');
            }
        }).finally(() => setLoading(false));
    };

    const resetPassword = async e => {
       setLoading(true);
        e.preventDefault();
        makeRequest({
            method: 'POST',
            url: '/api/password-reset/request',
            data: { email: email },
        }).then(res => {
            alert(t(res?.data?.success
                ? 'password_reset_email_sent'
                : 'error_occurred'));
            setEmail('');
        }).finally(() => setLoading(false));
    };

    if (user === undefined) {
        return <></>;
    }

    if (user) {
        navigate('/admin/dashboard');
        return null;
    }

    return <div className="login-page">
        <form id="login-form" className="card v-spacing" onSubmit={reset_password ? resetPassword : submitLogin}>
            {logo && <img src={logo}/>}
            <div className="input-group">
                <label htmlFor="email">{t('email')}</label>
                <input id="email" type="email" name="email" placeholder="johndoe@mail.com" value={email} maxLength="255" onChange={e => setEmail(e.target.value)}/>
            </div>
            {!reset_password && <div className="input-group">
                <label htmlFor="password">{t('password')}</label>
                <input id="password" type="password" name="password" value={password} onChange={e => setPassword(e.target.value)}/>
            </div>}
            <button type="submit" disabled={loading}>{t(reset_password ? 'reset_password' : 'sign_in')}</button>
            <button type="button" className="pointer light" onClick={() => setResetPassword(!reset_password)} disabled={loading}>{t(reset_password ? 'go_back' : 'forgot_password')}</button>
        </form>
    </div>;
}