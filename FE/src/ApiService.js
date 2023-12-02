import axios from "axios";

const BE_URL = 'http://127.0.0.1:8000/api/';

const generateEndpointUrl = (endpoint) => {
  return `${BE_URL}${endpoint}`;
};

class ApiService {
    getNewsSources = () => {
        return axios.get(generateEndpointUrl('sources'))
            .then(response => response)
            .catch(error => error)
    }
    getNewsCategories = () => {
        return axios.get(generateEndpointUrl('categories'))
            .then(response => response)
            .catch(error => error)
    }

    getNews = (params) => {
        return axios.get(generateEndpointUrl('articles'), {
            headers: {},
            params: params,
        })
            .then(response => response)
            .catch(error => error)
    }

    getUser = () => {
        const userToken = localStorage.getItem('userToken');
        return axios.get(generateEndpointUrl('user'), {
            headers: {
                Authorization: `Bearer ${userToken}`,
            },
            params: {},
        })
            .then(response => response)
            .catch(error => error)
    }

    login = (email, password) => {
        return axios.post(generateEndpointUrl('auth/login'), {
            email: email,
            password: password
        })
            .then(response => response)
            .catch(error => error)
    }

    logout = () => {
        const userToken = localStorage.getItem('userToken');
        return axios.post(generateEndpointUrl('auth/logout'), {}, {
            headers: {
                Authorization: `Bearer ${userToken}`,
            }
        })
            .then(response => {
                localStorage.removeItem('userToken');
                return response;
            })
            .catch(error => error)
    }

    register = (name, email, password) => {
        return axios.post(generateEndpointUrl('auth/register'), {
            name: name,
            email: email,
            password: password
        })
            .then(response => response)
            .catch(error => error)
    }

    setUserPreferredSearch = (searchParams) => {
        const userToken = localStorage.getItem('userToken');
        return axios.post(generateEndpointUrl('user-preference'),
            {'preference':searchParams},
            {
                headers: {
                    Authorization: `Bearer ${userToken}`,
                }
            })
            .then(response => response)
            .catch(error => error)
    }
    getUserPreferredSearch = () => {
        const userToken = localStorage.getItem('userToken');
        return axios.get(generateEndpointUrl('user-preference'),{
            headers: {
                Authorization: `Bearer ${userToken}`,
            },
            params: {},
        })
            .then(response => response)
            .catch(error => error)
    }
}

export default new ApiService;