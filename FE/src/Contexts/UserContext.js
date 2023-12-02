import React from "react"
import ApiService from "../ApiService";

export const CurrentUserContext = React.createContext()

export const CurrentUserProvider = ({children}) => {
    const [currentUser, setCurrentUser] = React.useState(null)

    const fetchCurrentUser = () => {
        ApiService.getUser()
            .then(response => setCurrentUser(response?.data?.data?.user))
            .catch(console.error);
    }

    return (
        <CurrentUserContext.Provider value={{currentUser, fetchCurrentUser}}>
            {children}
        </CurrentUserContext.Provider>
    )
}

export const useCurrentUser = () => React.useContext(CurrentUserContext)