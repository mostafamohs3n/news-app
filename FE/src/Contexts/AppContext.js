import React from "react"

export const AppContext = React.createContext();

export const AppProvider = ({children}) => {
    const [newsQueryParams, setNewsQueryParams] = React.useState({
        q: '',
        from_date: null,
        to_date: null,
        sources: [],
        external_sources: [],
        categories: [],
    })

    return (
        <AppContext.Provider value={{newsQueryParams, setNewsQueryParams}}>
            {children}
        </AppContext.Provider>
    )
}

export const useAppParams = () => React.useContext(AppContext)