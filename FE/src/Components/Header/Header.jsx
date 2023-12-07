import React, {useState} from "react";
import {Button, Container, Nav, Navbar} from "react-bootstrap";
import {useCurrentUser} from "../../Contexts/UserContext";
import LoginModal from "../LoginModal/LoginModal";
import ApiService from "../../ApiService";
import RegisterModal from "../RegisterModal/RegisterModal";
import toast from "react-hot-toast";

const Header = ({}) => {
    const {currentUser, fetchCurrentUser} = useCurrentUser();
    const [showLoginModal, setShowLoginModal] = useState(false);
    const [showRegisterModal, setShowRegisterModal] = useState(false);
    React.useEffect(() => fetchCurrentUser(), []);

    const handleLogout = async () => {
        ApiService.logout()
            .then((response) => {
                if(response?.data?.success){
                    fetchCurrentUser();
                    window.location.reload();
                }else{
                    toast.error("Something went wrong while logging out.")
                }
            })
            .catch(error => {
                toast.error("Something went wrong while logging out.")
            });
    }
    return (
        <>
            <Navbar bg="primary" expand="lg" className="mb-4 p-2">
                <Container>
                    <Navbar.Brand href="#home"><b>News</b></Navbar.Brand>
                    <Navbar.Toggle aria-controls="basic-navbar-nav"/>
                    <Navbar.Collapse id="basic-navbar-nav">
                        <Nav className="me-auto">
                            <Nav.Link href="/">Home</Nav.Link>
                        </Nav>
                    </Navbar.Collapse>
                    <Navbar.Collapse className="justify-content-end">
                        {currentUser ?
                            <>
                                <Nav>
                                    <Nav.Link>Signed in as: {currentUser?.name}</Nav.Link>
                                    <Nav.Link className={'text-bg-light'} onClick={handleLogout}>Logout</Nav.Link>
                                </Nav>
                            </>
                        : null}
                        {
                            !currentUser
                                ? <Nav>
                                    <Nav.Link className={'text-bg-light'} onClick={() => setShowLoginModal(true)}>Login</Nav.Link>
                                    <Nav.Link className={'text-bg-light mx-2'} onClick={() => setShowRegisterModal(true)}>Register</Nav.Link>
                                </Nav>
                                : null
                        }

                    </Navbar.Collapse>
                </Container>
            </Navbar>
            <LoginModal show={showLoginModal} setShow={setShowLoginModal}/>
            <RegisterModal show={showRegisterModal} setShow={setShowRegisterModal}/>
        </>
    );
};
export default Header;