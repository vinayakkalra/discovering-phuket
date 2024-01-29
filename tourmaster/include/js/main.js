

console.log("hello");

function toHex(rawData) {
    console.log("trigger");
    return Array.from(new Uint8Array(rawData))
        .map((byte) => byte.toString(16).padStart(2, '0'))
        .join('');
}


// const loginButton = document.getElementsByClassName('icp_self_login');
// console.log(loginButton[2])
// loginButton[2].addEventListener('click', async () => {
//     console.log("button clicked");
//     try {
//         const response = await window.ic?.plug?.requestConnect();
//         console.log(response);
//         if (response && response.rawKey && response.rawKey.data) {
//             let rawKeyHex = toHex(response.rawKey.data);
//             console.log("this is rawKeyHex:", rawKeyHex);
//             let principal = await window.ic.plug.agent.getPrincipal();
//             let principalText = principal.toText();
//             console.log("id:", principalText);
//         }
//     } catch (error) {
//         console.error("Error in onBtnConnect:", error);
//     }
// });

async function loginWithPlug() {
    console.log("button clicked - Login with Plug");
    try {
        const response = await window.ic?.plug?.requestConnect();
        console.log(response);
        if (response && response.rawKey && response.rawKey.data) {
            let rawKeyHex = toHex(response.rawKey.data);
            console.log("this is rawKeyHex:", rawKeyHex);
            let principal = await window.ic.plug.agent.getPrincipal();
            let accountId = await window.ic.plug.accountId;
            let principalText = principal.toText();
            //let accountIdText = accountId.toText();
            sessionStorage.setItem("principalId", principalText);
            sessionStorage.setItem("accountId", accountId);
            console.log("id:", principalText);
        }
    } catch (error) {
        console.error("Error in onBtnConnect:", error);
    }
}

async function checkNft() {
    console.log("button clicked - check nft");
    try {
        
    } catch (error) {
        console.error("Error in onBtnConnect:", error);
    }
}
