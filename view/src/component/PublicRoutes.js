import React from 'react';

import { Navigate, Outlet } from 'react-router-dom'

const useAuth = () => {
    const user = localStorage.getItem('user')
    if (user) {
        return true
    } else {
        return false
    }
}

const PublicRoutes = (props) => {
    return <Outlet />
}

export default PublicRoutes;