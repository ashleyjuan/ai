import React from 'react';
import ReactDOM from 'react-dom';
import 'bootstrap/dist/css/bootstrap.css';
import axios from 'axios';
import { BrowserRouter } from "react-router-dom";
import App from './App';

//  axios.defaults.baseURL = "http://localhost:8083"; 
axios.defaults.baseURL = "";


ReactDOM.render(
    <BrowserRouter>
        <App></App>
    </BrowserRouter>,

    document.getElementById('root')
);


