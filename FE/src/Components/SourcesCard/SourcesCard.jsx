import React, {useEffect, useState} from "react";
import {Card, Col, Form} from "react-bootstrap";
import ApiService from "../../ApiService";
import Select from "react-select";
import {useAppParams} from "../../Contexts/AppContext";
import toast from "react-hot-toast";

const SourcesCard = ({}) => {
    const [sources, setSources] = useState([]);
    const [selectedSources, setSelectedSources] = useState([]);
    const [selectedExternalSources, setSelectedExternalSources] = useState([]);
    const {newsQueryParams, setNewsQueryParams} = useAppParams();


    useEffect(() => {
        ApiService.getNewsSources()
            .then(response => {
                setSources(response?.data?.data)
            })
            .catch(e => {
                console.log(e)
                toast.error("Something went wrong while fetching data.")
            })
    }, []);

    useEffect(() => {
        setNewsQueryParams(prevState => {
            return {
                ...prevState,
                sources: selectedSources,
                external_sources: selectedExternalSources,
            }
        })
    }, [selectedSources]);

    useEffect(() => {
        if (newsQueryParams['sources']) {
            setSelectedSources(newsQueryParams['sources']);
            setSelectedExternalSources(newsQueryParams['external_sources']);
        }
    }, [newsQueryParams]);

    if (!sources || !sources.length) {
        return null;
    }

    const getDefaultValue = () => {
        let sourcesValue = sources.filter((source) => !source.external_source_parent_id && selectedSources.includes(source.id));
        let externalSourcesValue = sources.filter((source) => source.external_source_parent_id > 0 && selectedExternalSources.includes(source.id));

        return [...sourcesValue, ...externalSourcesValue] ?? [];
    }

    const handleSelectChange = (values) => {
        let sourcesSelected = new Set(),
            externalSourcesSelected = new Set();
        values.map(val => {
            if (val.external_source_parent_id > 0) {
                externalSourcesSelected.add(val.id);
            } else {
                sourcesSelected.add(val.id);
            }
        })
        setSelectedSources([...sourcesSelected]);
        setSelectedExternalSources([...externalSourcesSelected]);
    }

    return (
        <Card className="shadow mb-3">
            <Card.Body>
                <Card.Title>Sources</Card.Title>
                <Card.Subtitle className="mb-2 text-muted">Select Sources</Card.Subtitle>
                <Form.Group as={Col}>
                    <Select
                        options={sources}
                        getOptionValue={option => option.id}
                        getOptionLabel={option => option.name}
                        defaultValue={getDefaultValue()}
                        value={getDefaultValue()}
                        isMulti
                        closeMenuOnSelect={false}
                        onChange={handleSelectChange}
                    />
                </Form.Group>
            </Card.Body>
        </Card>
    );
};
export default SourcesCard;