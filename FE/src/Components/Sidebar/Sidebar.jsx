import {Card} from "react-bootstrap";
import SourcesCard from "../SourcesCard/SourcesCard";
import CategoriesCard from "../CategoriesCard/CategoriesCard";
import AuthorsCard from "../AuthorsCard/AuthorsCard";

const Sidebar = ({}) => {
    return (
        <div id="sidebar">
            <SourcesCard/>
            <CategoriesCard/>
            <AuthorsCard />
        </div>
    );
}
export default Sidebar;