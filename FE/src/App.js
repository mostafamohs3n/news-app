import React from "react";
import Header from "./Components/Header/Header";
import Sidebar from "./Components/Sidebar/Sidebar";
import {Container, Row} from "react-bootstrap";
import NewsList from "./Components/News/NewsList";
import {CurrentUserProvider} from "./Contexts/UserContext";
import ContentFilter from "./Components/ContentFilter/ContentFilter";
import {AppProvider} from "./Contexts/AppContext";
import Footer from "./Components/Footer/Footer";
import {Toaster} from "react-hot-toast";


function App() {
    return (
        <AppProvider>
            <CurrentUserProvider>
                <div><Toaster/></div>
                <div className="App">
                    <Header/>
                    <div id="main-content">
                        <Container>
                            <Row>
                                <div id="main-content-filter">
                                    <div className="col-md-12">
                                        <ContentFilter/>
                                    </div>
                                </div>
                            </Row>
                        </Container>
                        <Container>
                            <Row>
                                <div className="col-md-3">
                                    <Sidebar/>
                                </div>
                                <div className="col-md-9">
                                    <NewsList/>
                                </div>
                            </Row>
                        </Container>
                        <Container>
                            <Row>
                                <Footer/>
                            </Row>
                        </Container>
                    </div>
                </div>
            </CurrentUserProvider>
        </AppProvider>
    );
}

export {
    App as default
};
