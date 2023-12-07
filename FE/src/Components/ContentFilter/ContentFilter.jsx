import React, {useEffect} from "react";
import {Button, Card, Form} from "react-bootstrap";
import {Controller, useForm} from "react-hook-form";
import "react-datepicker/dist/react-datepicker.css"
import DatePicker from "react-datepicker";
import {useAppParams} from "../../Contexts/AppContext";
import {format} from "date-fns";
import {useCurrentUser} from "../../Contexts/UserContext";
import ApiService from "../../ApiService";
import toast from "react-hot-toast";

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
        if (newsQueryParams['from_date']) {
            setValue('from_date', new Date(newsQueryParams['from_date']));
        }
        if (newsQueryParams['to_date']) {
            setValue('to_date', new Date(newsQueryParams['to_date']));
        }
    }, [newsQueryParams]);

    const {currentUser} = useCurrentUser();

    const handleSaveFilter = () => {
        if (!currentUser) {
            toast.error("Login to save your preferred search.");
            return;
        }
        const {page, ...searchParams} = newsQueryParams;
        ApiService.setUserPreferredSearch(searchParams)
            .then((response) => {
                if (response) {
                    toast.success("Filter saved successfully.");
                } else {
                    toast.error("Failed to save preference.")
                }
            })
            .catch(error => {
                toast.error("Something went wrong while saving preferences.")
            });
    }

    const onSubmit = ({q, from_date, to_date}) => {
        if (from_date) {
            from_date = format(new Date(from_date), 'yyyy-MM-dd');
        }
        if (to_date) {
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
                            <div className="p-2 w-50">
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
                                                isClearable={getValues('from_date') ? true : false}
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
                                                isClearable={getValues('to_date') ? true : false}
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
                            <div className="p-2 pt-3">
                                <Form.Label>&nbsp;</Form.Label>
                                <Button type="submit" size="md" variant="outline-primary">Search</Button>
                            </div>
                            <div className="p-2 pt-3">
                                <Form.Label>&nbsp;</Form.Label>
                                <Button onClick={handleSaveFilter} variant="outline-dark" size="md">
                                    Save
                                </Button>
                            </div>
                        </div>
                    </Form>
                </Card.Body>
            </Card>
        </>
    );
};
export default ContentFilter;