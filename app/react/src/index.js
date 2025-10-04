import React from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter, Route, Routes } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import AdminPages from './components/AdminPages';
import Login from './pages/Login';
import Dashboard from './pages/Dashboard';

const App = () => {
    const query_client = new QueryClient();

    return <BrowserRouter>
        <QueryClientProvider className="app" client={query_client}>
            <Routes>
                <Route path="/console/login" element={<Login/>}/>
                <Route path="/console" element={<AdminPages/>}>
                    <Route path="dashboard" element={<Dashboard/>}/>
                    <Route path="" element={<Dashboard/>}/>
                </Route>
                <Route path="*" element={<div>404 Not Found</div>}/>
            </Routes>
        </QueryClientProvider>
    </BrowserRouter>;
};

createRoot(document.getElementById('root')).render(<App/>);
