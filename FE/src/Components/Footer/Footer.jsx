const Footer = () => {
    return (
        <div className="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top">
            <div className="col-md-4 d-flex align-items-center">
                <div>
                    <div><span className="mb-3 mb-md-0">© {new Date().getFullYear()} News Aggregator</span></div>
                    <div><span className="mb-3 mb-md-0 text-muted">Github: @mostafamohs3n</span></div>
                </div>
            </div>
        </div>
    )
}
export default Footer;