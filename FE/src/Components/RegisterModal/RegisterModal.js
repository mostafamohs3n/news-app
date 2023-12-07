import React, {useState} from "react";
import {Button, Form, Modal} from "react-bootstrap";
import {Controller, useForm} from "react-hook-form";
import {useCurrentUser} from "../../Contexts/UserContext";
import ApiService from "../../ApiService";
import toast from "react-hot-toast";

const RegisterModal = ({show, setShow}) => {
    const {currentUser, fetchCurrentUser} = useCurrentUser();
    const defaultValues = {
        email: '',
        password: '',
    };
    const [formErrors, setFormErrors] = useState(null);
    const {register, control, handleSubmit, watch, formState: {errors}} = useForm({defaultValues: defaultValues});
    const onSubmit = ({name, email, password}) => {
        ApiService.register(name, email, password)
            .then(response => {
                if(response?.response?.data?.message){
                    toast.error(response?.response?.data?.message || "Failed to create an account");
                    return setFormErrors(response?.response?.data?.errors);
                }else{
                    if(response?.data?.data?.token) {
                        localStorage.setItem('userToken', response?.data?.data?.token);
                        fetchCurrentUser();
                        return setTimeout(() => {
                            setShow(false);
                        }, 1000);
                    }
                }
            })
            .catch(error => {
                toast.error("Something went wrong while creating an account.")
            });

    };


    return (
        <Modal show={show} onHide={() => setShow(false)}>
            <Form onSubmit={handleSubmit(onSubmit)}>
                <Modal.Header closeButton>
                    <Modal.Title>{currentUser ? 'Successfully Registered!': 'Register'}</Modal.Title>
                </Modal.Header>
                <Modal.Body>
                    <Form.Group>
                        <Form.Label>Name</Form.Label>
                        <Controller
                            control={control}
                            name={'name'}
                            rules={{"required": "Your Name is required"}}
                            render={
                                ({field: {onChange, value, ref}}) => (
                                    <Form.Control type={'text'} onChange={onChange} value={value} ref={ref}
                                                  isInvalid={errors?.name} placeholder="Mostafa Mohsen"/>
                                )
                            }/>
                        <Form.Text className='text-danger'>{errors?.name?.message}</Form.Text>
                    </Form.Group>
                    <Form.Group>
                        <Form.Label>Email</Form.Label>
                        <Controller
                            control={control}
                            name={'email'}
                            rules={{"required": "Your email is required"}}
                            render={
                                ({field: {onChange, value, ref}}) => (
                                    <Form.Control type={'email'} onChange={onChange} value={value} ref={ref}
                                                  isInvalid={errors?.email} placeholder="mostafa.mohsen73@gmail.com"/>
                                )
                            }/>
                        <Form.Text className='text-danger'>{errors?.email?.message || formErrors?.email?.[0]}</Form.Text>
                    </Form.Group>
                    <Form.Group>
                        <Form.Label>Password</Form.Label>
                        <Controller
                            control={control}
                            name='password'
                            rules={{"required": "Your password is required"}}
                            render={
                                ({field: {onChange, value, ref}}) => (
                                    <Form.Control type='password' onChange={onChange} value={value} ref={ref}
                                                  isInvalid={errors?.email} placeholder="******"/>
                                )
                            }/>
                        <Form.Text className='text-danger'>{errors?.password?.message || formErrors?.password?.[0]}</Form.Text>
                    </Form.Group>
                </Modal.Body>
                <Modal.Footer>
                    <Button variant="secondary" onClick={() => setShow(false)}>
                        Close
                    </Button>
                    <Button variant="primary" type="submit">
                        Register
                    </Button>
                </Modal.Footer>
            </Form>
        </Modal>
    );
};
export default RegisterModal;