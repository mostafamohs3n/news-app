import React, {useEffect} from "react";
import {Button, Card, Form} from "react-bootstrap";
import {Controller, useForm} from "react-hook-form";
import "react-datepicker/dist/react-datepicker.css"
import DatePicker from "react-datepicker";
import {useAppParams} from "../../Contexts/AppContext";
import {format} from "date-fns";
import {useCurrentUser} from "../../Contexts/UserContext";
import ApiService from "../../ApiService";

const ContentFilter = () => {
    const defaultValues = {
        'q': '',
        'from_date': '',
        'to_date': ''
    };
    const {
        getValues,
        control,
        handleSubmit,
        setValue,
        formState: {
            errors
        }
    } = useForm({defaultValues: defaultValues});

    const {newsQueryParams, setNewsQueryParams} = useAppParams();

    useEffect(() => {
        setValue('q', newsQueryParams['q']);
        if(newsQueryParams['from_date']) {
            setValue('from_date', new Date(newsQueryParams['from_date']));
        }
        if(newsQueryParams['to_date']) {
            setValue('to_date', new Date(newsQueryParams['to_date']));
        }
    }, [newsQueryParams]);

    const {currentUser} = useCurrentUser();

    const handleSaveFilter = () => {
        if (!currentUser) {
            alert("Login to save your preferred search.");
            return;
        }
        const {page, ...searchParams} = newsQueryParams;
        ApiService.setUserPreferredSearch(searchParams)
            .then((response) => {
                if (response) {
                    alert("Saved successfully.");
                } else {
                    alert("Something went wrong while saving preference.")
                }
            })
            .catch(console.error);
    }

    const onSubmit = ({q, from_date, to_date}) => {
        if(from_date) {
            from_date = format(new Date(from_date), 'yyyy-MM-dd');
        }
        if(to_date) {
            to_date = format(new Date(to_date), 'yyyy-MM-dd');
        }
        setNewsQueryParams((prevState) => {
            return {
                ...prevState,
                ...{q, from_date, to_date}
            }
        })
    };

    return (
        <>
            <Card className="shadow mb-5 mt-2 p-4">
                <Card.Body>
                    <Form onSubmit={handleSubmit(onSubmit)}>
                        <div className="d-flex flex-row w-100">
                            <div className="p-2 w-75">
                                <Form.Label>&nbsp;</Form.Label>
                                <Form.Label className="d-lg-none">Keyword</Form.Label>
                                <Controller
                                    control={control}
                                    name='q'
                                    render={
                                        ({field: {onChange, value, ref}}) => (
                                            <Form.Control size="lg" type={'text'} onChange={onChange} value={value}
                                                          ref={ref}
                                                          isInvalid={errors?.q} placeholder="Search"/>
                                        )
                                    }/>
                            </div>
                            <div className="p-2">
                                <Form.Label>From Date</Form.Label>
                                <Controller
                                    control={control}
                                    name='from_date'
                                    defaultValue={null}
                                    render={
                                        ({field: {onChange, value, ref}}) => (
                                            <DatePicker
                                                selected={value}
                                                onChange={onChange}
                                                selectsStart
                                                startDate={getValues('from_date')}
                                                endDate={getValues('to_date')}
                                                dateFormat="yyyy-MM-dd"
                                                isClearable
                                                customInput={
                                                    <input
                                                        className="form-control form-control-lg"
                                                        type="text"
                                                        id="from_date"
                                                        placeholder="From Date"/>
                                                }
                                            />
                                        )
                                    }/>
                            </div>
                            <div className="p-2">
                                <Form.Label>End Date</Form.Label>
                                <Controller
                                    control={control}
                                    name='to_date'
                                    defaultValue={null}
                                    render={
                                        ({field: {onChange, value, ref}}) => (
                                            <DatePicker
                                                selected={value}
                                                onChange={onChange}
                                                selectsEnd
                                                startDate={getValues('from_date')}
                                                endDate={getValues('to_date')}
                                                minDate={getValues('from_date')}
                                                dateFormat="yyyy-MM-dd"
                                                isClearable
                                                customInput={
                                                    <input
                                                        className="form-control form-control-lg"
                                                        type="text"
                                                        id="to_date"
                                                        placeholder="To Date"/>
                                                }
                                            />
                                        )
                                    }/>
                            </div>
                            <div className="p-2">
                                <Form.Label>&nbsp;</Form.Label>
                                <Button type="submit" size="lg" variant="warning">Search</Button>
                            </div>
                        </div>
                        <div className="d-flex justify-content-end px-2">
                            <Button onClick={handleSaveFilter} variant="outline-dark" size="sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                     className="bi bi-save" viewBox="0 0 16 16">
                                    <path
                                        d="M2 1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H9.5a1 1 0 0 0-1 1v7.293l2.646-2.647a.5.5 0 0 1 .708.708l-3.5 3.5a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L7.5 9.293V2a2 2 0 0 1 2-2H14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h2.5a.5.5 0 0 1 0 1H2z"/>
                                </svg>
                                {` `}Save Filter</Button>
                        </div>

                    </Form>
                </Card.Body>
            </Card>
        </>
    );
};
export default ContentFilter;