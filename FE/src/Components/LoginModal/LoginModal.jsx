import React from "react";
import {Button, Form, Modal} from "react-bootstrap";
import {Controller, useForm} from "react-hook-form";
import {useCurrentUser} from "../../Contexts/UserContext";
import ApiService from "../../ApiService";

const LoginModal = ({show, setShow}) => {
    const {currentUser, fetchCurrentUser} = useCurrentUser();
    const defaultValues = {
        email: '',
        password: '',
    };
    const {register, control, handleSubmit, watch, formState: {errors}} = useForm({defaultValues: defaultValues});
    const onSubmit = ({email, password}) => {
        ApiService.login(email, password)
            .then((response, errors) => {
                console.log(response, errors);
                if(!response || response?.response?.data?.message){
                    return alert(response?.response?.data?.message || "E");
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
                alert("TEST!");
                alert(JSON.stringify(e));
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
                        <Form.Text className='text-danger'>{errors?.email?.message}</Form.Text>
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
                        <Form.Text className='text-danger'>{errors?.password?.message}</Form.Text>
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