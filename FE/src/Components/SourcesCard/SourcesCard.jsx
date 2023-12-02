import React, {useEffect, useState} from "react";
import {Card, Col, Form} from "react-bootstrap";
import ApiService from "../../ApiService";
import Select from "react-select";
import {useAppParams} from "../../Contexts/AppContext";

const SourcesCard = ({}) => {

    const [sources, setSources] = useState([]);
    const [selectedSources, setSelectedSources] = useState([]);
    const {newsQueryParams, setNewsQueryParams} = useAppParams();


    useEffect(() => {
        ApiService.getNewsSources()
            .then(response => {
                setSources(response?.data?.data)
            })
            .catch(e => console.log(e))
    }, []);

    useEffect(() => {
        setNewsQueryParams(prevState => {
            return {
                ...prevState,
                sources: selectedSources
            }
        })
    }, [selectedSources]);

    useEffect(() => {
        if(newsQueryParams['sources']) {
            setSelectedSources(newsQueryParams['sources']);
        }
    }, [newsQueryParams]);

    if(!sources || !sources.length){
        return null;
    }
    const getDefaultValue = () => {
        return sources.filter((source) => selectedSources.includes(source.identifier));
    }
    return (
        <Card className="shadow mb-3">
            <Card.Body>
                <Card.Title>Sources</Card.Title>
                <Card.Subtitle className="mb-2 text-muted">Select Sources</Card.Subtitle>
                     <Form.Group as={Col}>
                        <Select
                            options={sources}
                            getOptionValue={option => option.identifier}
                            getOptionLabel={option => option.name}
                            defaultValue={getDefaultValue()}
                            value={getDefaultValue()}
                            isMulti
                            closeMenuOnSelect={false}
                            onChange={values => setSelectedSources(values.map(val => val.identifier))}
                        />
                    </Form.Group>
            </Card.Body>
        </Card>
    );
};
export default SourcesCard;