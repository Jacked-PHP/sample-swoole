
<style>
    #chat-container {
        position: fixed;
        right: 1px;
        bottom: 1px;
        border: 1px solid #000;
        padding: 10px;
    }

    #output strong {
        margin-right: 10px;
    }
</style>

<div id="chat-container">
    <div>

        <form id="message-form">
            <div>
                <input id="message-box" type="text" placeholder="The message goes here..."/>
            </div>
            <input type="submit" value="Submit"/>
        </form>

    </div>

    <hr/>

    <div>
        <ul id="output"></ul>
    </div>
</div>
