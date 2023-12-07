import React, {useEffect, useState} from "react";
import {Card, Col, Form} from "react-bootstrap";
import ApiService from "../../ApiService";
import Select from "react-select";
import {useAppParams} from "../../Contexts/AppContext";
import toast from "react-hot-toast";

const CategoriesCard = ({}) => {

    const [categories, setCategories] = useState([]);
    const [selectedCategories, setSelectedCategories] = useState([]);
    const {newsQueryParams ,setNewsQueryParams} = useAppParams();

    useEffect(() => {
        ApiService.getNewsCategories()
            .then(response => {
                setCategories(response?.data?.data)
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
                categories: selectedCategories
            }
        })
    }, [selectedCategories]);


    useEffect(() => {
        if(newsQueryParams['categories']) {
            setSelectedCategories(newsQueryParams['categories']);
        }
    }, [newsQueryParams]);


    if (!categories) {
        return null;
    }

    const getDefaultValue = () => {
        return categories.filter((category) => selectedCategories.includes(category.identifier));
    }
    return (
        <Card className="shadow mb-3">
            <Card.Body>
                <Card.Title>Categories</Card.Title>
                <Card.Subtitle className="mb-2 text-muted">Select Categories</Card.Subtitle>
                    <Form.Group as={Col}>
                        <Select
                            options={categories}
                            getOptionValue={option => option.identifier}
                            getOptionLabel={option => option.name}
                            isMulti
                            defaultValue={getDefaultValue()}
                            value={getDefaultValue()}
                            closeMenuOnSelect={false}
                            onChange={values => setSelectedCategories(values.map((val) => val.identifier))}
                        />
                    </Form.Group>
            </Card.Body>
        </Card>
    );
};
export default CategoriesCard;