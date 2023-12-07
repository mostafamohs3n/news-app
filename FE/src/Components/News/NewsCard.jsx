import React, {useEffect, useState} from "react";
import {Card} from "react-bootstrap";
import ApiService from "../../ApiService";
import toast from "react-hot-toast";

const NewsCard = ({newsInfo}) => {
    const {id, title, excerpt, contentUrl, thumbnail, date, source, externalSource, category, author} = newsInfo;

    const [news, setNews] = useState({});


    useEffect(() => {
        ApiService.getNews({})
            .then(response => setNews(response?.data?.data))
            .catch(e => {
                toast.error("Something went wrong while fetching data.")
            })
    }, []);

    if (!news) {
        return null;
    }
    return (
        <Card className="shadow mb-5">
            <Card.Img
                variant="top"
                src={thumbnail || 'https://8bppl.ca/storage/images/news/news-default.png'}
                style={{maxHeight: '250px', objectFit: 'cover', objectPosition: 'center'}}
            />
            <Card.Body>
                <Card.Title>{title}</Card.Title>
                <Card.Subtitle className="mb-2 text-muted">{(externalSource || source || category).toUpperCase()}</Card.Subtitle>
                <p><small>Published on {new Date(date).toLocaleDateString('en-us')}</small></p>
                <p>{excerpt}</p>
                <p>
                    <a
                        className="btn btn-primary"
                        href={contentUrl}
                        target={"_blank"}>
                        Read more on <b>{externalSource || source || "the news website"}</b>
                    </a>
                </p>
                <Card.Footer>
                    <p>{typeof author == 'string' && author !== '' ? `By ${author}` : ''}</p>
                </Card.Footer>
            </Card.Body>
        </Card>
    );
};
export default NewsCard;