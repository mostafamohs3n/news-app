import React, {useEffect, useState} from "react";
import {Card, Col, Form} from "react-bootstrap";
import ApiService from "../../ApiService";
import Select from "react-select";
import {useAppParams} from "../../Contexts/AppContext";
import toast from "react-hot-toast";

const AuthorsCard = ({}) => {
    const [authors, setAuthors] = useState([]);
    const [selectedAuthors, setSelectedAuthors] = useState([]);
    const {newsQueryParams, setNewsQueryParams} = useAppParams();


    useEffect(() => {
        ApiService.getNewsAuthors()
            .then(response => {
                setAuthors(response?.data?.data)
            })
            .catch(e => {
                console.log(e);
                toast.error("Something went wrong while fetching data.")
            })
    }, []);

    useEffect(() => {
        setNewsQueryParams(prevState => {
            return {
                ...prevState,
                authors: selectedAuthors,
            }
        })
    }, [selectedAuthors]);

    useEffect(() => {
        if (newsQueryParams['authors']) {
            setSelectedAuthors(newsQueryParams['authors']);
        }
    }, [newsQueryParams]);

    if (!authors || !authors.length) {
        return null;
    }

    const getDefaultValue = () => {
        return authors.filter((author) => selectedAuthors.includes(author.name));
    }

    return (
        <Card className="shadow mb-3">
            <Card.Body>
                <Card.Title>Authors</Card.Title>
                <Card.Subtitle className="mb-2 text-muted">Select Authors</Card.Subtitle>
                <Form.Group as={Col}>
                    <Select
                        options={authors}
                        getOptionValue={option => option.name}
                        getOptionLabel={option => option.name}
                        defaultValue={getDefaultValue()}
                        value={getDefaultValue()}
                        isMulti
                        closeMenuOnSelect={false}
                        onChange={values => setSelectedAuthors(values.map((val) => val.name))}
                    />
                </Form.Group>
            </Card.Body>
        </Card>
    );
};
export default AuthorsCard;