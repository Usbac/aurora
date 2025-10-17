import React from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter, Route, Routes } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import AdminPages from './components/AdminPages';
import Login from './pages/Login';
import Dashboard from './pages/Dashboard';
import Settings from './pages/Settings';
import Links from './pages/tables/Links';
import Link from './pages/Link';
import Tags from './pages/tables/Tags';
import Pages from './pages/tables/Pages';
import Posts from './pages/tables/Posts';

const App = () => {
    const query_client = new QueryClient();

    return <BrowserRouter>
        <QueryClientProvider className="app" client={query_client}>
            <Routes>
                <Route path="/console" element={<Login/>}/>
                <Route path="/console" element={<AdminPages/>}>
                    <Route path="dashboard" element={<Dashboard/>}/>
                    <Route path="pages" element={<Pages/>}/>
                    <Route path="posts" element={<Posts/>}/>
                    <Route path="links" element={<Links/>}/>
                    <Route path="tags" element={<Tags/>}/>
                    <Route path="settings" element={<Settings/>}/>
                    <Route path="links/edit" element={<Link/>}/>
                </Route>
                <Route path="*" element={<div>404 Not Found</div>}/>
            </Routes>
        </QueryClientProvider>
    </BrowserRouter>;
};

createRoot(document.getElementById('root')).render(<App/>);
