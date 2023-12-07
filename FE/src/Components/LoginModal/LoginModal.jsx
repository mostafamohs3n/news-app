import React, {useState} from "react";
import {Button, Form, Modal} from "react-bootstrap";
import {Controller, useForm} from "react-hook-form";
import {useCurrentUser} from "../../Contexts/UserContext";
import ApiService from "../../ApiService";
import toast from "react-hot-toast";

const LoginModal = ({show, setShow}) => {
    const {currentUser, fetchCurrentUser} = useCurrentUser();
    const defaultValues = {
        email: '',
        password: '',
    };
    const [formErrors, setFormErrors] = useState(null);
    const {register, control, handleSubmit, watch, formState: {errors}} = useForm({defaultValues: defaultValues});
    const onSubmit = ({email, password}) => {
        ApiService.login(email, password)
            .then((response, errors) => {
                if(!response || response?.response?.data?.message){
                    toast.error(response?.response?.data?.message || "Failed to login");
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
            .catch((e) => {

            });

    };


    return (
        <Modal show={show} onHide={() => setShow(false)}>
            <Form onSubmit={handleSubmit(onSubmit)}>
                <Modal.Header closeButton>
                    <Modal.Title>{currentUser ? 'Successfully logged in': 'Login'}</Modal.Title>
                </Modal.Header>
                <Modal.Body>
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
                        <Form.Text className='text-danger'>{errors?.password?.message  || formErrors?.password?.[0]}</Form.Text>
                    </Form.Group>
                </Modal.Body>
                <Modal.Footer>
                    <Button variant="secondary" onClick={() => setShow(false)}>
                        Close
                    </Button>
                    <Button variant="primary" type="submit">
                        Login
                    </Button>
                </Modal.Footer>
            </Form>
        </Modal>
    );
};
export default LoginModal;