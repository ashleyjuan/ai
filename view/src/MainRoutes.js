import React, { useState, useEffect } from 'react';
import { Routes, Route, Navigate, Switch, Link } from "react-router-dom"
import PublicRoutes from "./component/PublicRoutes"

const MainRoutes = () => {
    return (
        <>
            1234212
            <Routes>
                {/* literacy */}
                <Route path="" element={<PublicRoutes />}>

                </Route>
            </Routes >
        </>

    )
}

export default MainRoutes

