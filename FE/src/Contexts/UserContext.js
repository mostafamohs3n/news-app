import React from "react"
import ApiService from "../ApiService";
import toast from "react-hot-toast";

export const CurrentUserContext = React.createContext()

export const CurrentUserProvider = ({children}) => {
    const [currentUser, setCurrentUser] = React.useState(null)

    const fetchCurrentUser = () => {
        // if(!currentUser){return;}
        ApiService.getUser()
            .then(response => setCurrentUser(response?.data?.data?.user))
            .catch(error => {
                toast.error("Something went wrong while fetching data.");
                console.log(error);
            });
    }

    return (
        <CurrentUserContext.Provider value={{currentUser, fetchCurrentUser}}>
            {children}
        </CurrentUserContext.Provider>
    )
}

export const useCurrentUser = () => React.useContext(CurrentUserContext)