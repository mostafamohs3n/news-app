import React, {useEffect, useState} from "react";
import ApiService from "../../ApiService";
import NewsCard from "./NewsCard";
import {useAppParams} from "../../Contexts/AppContext";
import {Alert, Pagination} from "react-bootstrap";

const NewsList = ({}) => {
    const [newsList, setNewsList] = useState({});
    const [userPreferredSearch, setUserPreferredSearch] = useState({});
    const {newsQueryParams, setNewsQueryParams} = useAppParams();
    const [page, setPage] = useState(1);

    useEffect(() => {
        ApiService.getNews({...newsQueryParams, page})
            .then(response => {
                setNewsList(response?.data?.data)
            })
            .catch(console.error)
            .catch(e => alert(e))
    }, [newsQueryParams, page]);

    useEffect(() => {
        if(!userPreferredSearch){
            return;
        }
        setNewsQueryParams(userPreferredSearch);
    }, [userPreferredSearch]);

    useEffect( () => {
        //attempt to get current user's preference
        ApiService.getUserPreferredSearch()
            .then(response =>
                setUserPreferredSearch(response?.data?.data?.preference)
            )
            .catch(console.error);
        // load news for first time.
        ApiService.getNews({...newsQueryParams, page})
            .then(response => {
                setNewsList(response?.data?.data)
            })
            .catch(e => alert(e))
    }, []);

    if (!newsList || !newsList.length) {
        return <>
            <Alert variant="info">
                No news found. Please try a different filter.
            </Alert>
        </>;
    }
    return (
        <>
            {newsList.map((newsInfo, i) => <NewsCard key={i} newsInfo={newsInfo}/>)}
            <div className="">
                <Pagination className="pagination justify-content-center">
                    <Pagination.First onClick={() => setPage(1)}/>
                    <Pagination.Prev disabled={page === 1} onClick={() => setPage(prevState => prevState > 1 ? prevState-1 : prevState)}/>
                    <Pagination.Item active>{page}</Pagination.Item>
                    <Pagination.Next disabled={!newsList.length} onClick={() => setPage(prevState => prevState +1)}/>
                </Pagination>
            </div>
        </>
    );
};
export default NewsList;