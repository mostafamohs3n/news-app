import {Card} from "react-bootstrap";
import SourcesCard from "../SourcesCard/SourcesCard";
import CategoriesCard from "../CategoriesCard/CategoriesCard";

const Sidebar = ({}) => {
    return (
        <div id="sidebar">
            <SourcesCard/>
            <CategoriesCard/>
        </div>
    );
}
export default Sidebar;