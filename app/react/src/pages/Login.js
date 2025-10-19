import React, { useState } from 'react';
import { makeRequest, useElement } from '../utils/utils';
import { useNavigate } from 'react-router-dom';

export default function Login() {
    const [ user ] = useElement('/api/v2/me');
    const logo = document.querySelector('meta[name="logo"]')?.content;
    const [ loading, setLoading ] = useState(false);
    const [ email, setEmail ] = useState('');
    const [ password, setPassword ] = useState('');
    const [ reset_password, setResetPassword ] = useState(false);
    const navigate = useNavigate();

    const submitLogin = async e => {
        setLoading(true);
        e.preventDefault();
        makeRequest({
            method: 'POST',
            url: '/api/v2/auth',
            data: {
                email: email,
                password: password,
            },
        }).then(res => {
            if (!res?.data?.success) {
                alert('Invalid email or password');
            } else {
                localStorage.setItem('auth_token', res.data.token);
                navigate('/console/dashboard');
            }
        }).finally(() => setLoading(false));
    };

    const resetPassword = async e => {
       setLoading(true);
        e.preventDefault();
        makeRequest({
            method: 'POST',
            url: '/api/v2/send_password_restore',
            data: {
                email: email,
            },
        }).then(res => {
            alert(res?.data?.success ? 'If the email is registered, you will receive an email with instructions to reset your password' : 'An error occurred, please try again later');
            setEmail('');
        }).finally(() => setLoading(false));
    };

    if (user === undefined) {
        return <></>;
    }

    if (user) {
        navigate('/console/dashboard');
        return null;
    }

    return <div className="login-page">
        <form id="login-form" className="card v-spacing" onSubmit={reset_password ? resetPassword : submitLogin}>
            {logo && <img src={logo}/>}
            <div className="input-group">
                <label htmlFor="email">Email</label>
                <input id="email" type="email" name="email" placeholder="johndoe@mail.com" value={email} maxLength="255" onChange={e => setEmail(e.target.value)}/>
            </div>
            {!reset_password && <div className="input-group">
                <label htmlFor="password">Password</label>
                <input id="password" type="password" name="password" value={password} onChange={e => setPassword(e.target.value)}/>
            </div>}
            <button type="submit" disabled={loading}>{reset_password ? 'Reset Password' : 'Sign In'}</button>
            <button type="button" className="pointer light" onClick={() => setResetPassword(!reset_password)} disabled={loading}>{reset_password ? 'Go Back' : 'Forgot Password?'}</button>
        </form>
    </div>;
}