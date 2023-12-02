import React, {useEffect, useState} from "react";
import {Card} from "react-bootstrap";
import ApiService from "../../ApiService";

const NewsCard = ({newsInfo}) => {
    const {id, title, excerpt, contentUrl, thumbnail, date, source, category, author} = newsInfo;

    const [news, setNews] = useState({});


    useEffect(() => {
        ApiService.getNews({})
            .then(response => setNews(response?.data?.data))
            .catch(e => alert(e))
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
                <Card.Subtitle className="mb-2 text-muted">{category || source}</Card.Subtitle>
                <p><small>Published on {new Date(date).toLocaleDateString('en-us')}</small></p>
                <p>{excerpt}</p>
                <p>
                    <a
                        className="btn btn-dark"
                        href={contentUrl}
                        target={"_blank"}>
                        Read more on {source || "the news website"}
                    </a>
                </p>
                <Card.Footer>
                    {typeof author == 'string' ? (author.toLowerCase().includes('by ') ? author : `By ${author}`) : ''}
                </Card.Footer>
            </Card.Body>
        </Card>
    );
};
export default NewsCard;