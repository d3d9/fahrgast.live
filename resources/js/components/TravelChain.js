import API from "../api/api";
window.TravelChain = class TravelChain {

    static destroy(chainId) {
        API.request(`/travelchain/${chainId}`, 'delete')
            .then(API.handleDefaultResponse)
            .then(() => {
                //delete status card if present
                let chainCard = document.getElementById(`chain-${chainId}`);
                if (chainCard) {
                    chainCard.remove();
                }

                //redirect to dashboard, if user is on chain page which is deleted
                if (window.location.pathname === `/travelchain/${chainId}`) {
                    window.location.href = '/dashboard';
                }
            });
    }

}
