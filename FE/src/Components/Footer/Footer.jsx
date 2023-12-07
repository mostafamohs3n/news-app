import {Badge} from "react-bootstrap";

const Footer = () => {
    return (
        <div className="w-100 d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top">
            <div className="col-md-4 d-flex align-items-center">
                <div>
                    <div><span className="mb-3 mb-md-0">All rights reserved. © {new Date().getFullYear()} <b>News</b></span></div>
                </div>

            </div>
            <div>
                <Badge bg={"primary"} className={"p-2"}>
                    <a className={"text-white"} href={"mailto:mostafa.mohsen73@gmail.com"}>
                        Contact Mostafa Mohsen
                    </a>
                </Badge>
            </div>
        </div>
    )
}
export default Footer;