<!--suppress HtmlFormInputWithoutLabel -->
<template>

    <div class="row">
        <div class="col-md-6">

            <div class="wrapper-sudoku">
                <div v-bind:class="'grid-sudoku grid-sudoku-' + size">

                    <div v-for="(row, rowIndex) in matrix"
                         v-bind:key="rowIndex"
                         class="grid-row"
                    >
                        <div v-for="cell in row"
                             v-bind:key="cell.id"
                             v-bind:class="[cell.error ? 'grid-cell-error' : '', 'grid-cell']"
                        >
                            <div v-if="cell.pid === null">
                                <input v-model="cell.val"
                                       v-bind:key="cell.id"
                                       v-on:change="makeMove(cell.id,cell.val)"
                                       type="text"
                                       class="grid-cell-editor"
                                />
                            </div>

                            <div v-else class="grid-cell-editor">
                                {{ cell.val }}
                            </div>

                        </div>
                    </div>

                </div>
            </div>

        </div>
        <div class="col-md-6">

            <div><b>Filled cells:</b> {{ cells.filled }}</div>
            <div><b>Empty cells:</b> {{ cells.empty }}</div>

            <br/><br/>

            <div class="sudoku-log">
                <div v-for="(message, index) in messages"
                     v-bind:key="index"
                >
                    {{ message }}
                </div>
            </div>

        </div>
    </div>
</template>

<script>
    let connection;
    let connectionErrorMessages = {};
    let connectionSuccessMessages = {};

    let connectionGetUuid = function () {
        return parseInt([Date.now(), Math.floor(Math.random() * 1000)].join(''));
    };

    let connectionSendMessage = function (
        method,
        params = [],
        successCallback = null,
        errorCallback = null
    ) {
        const id = connectionGetUuid();

        if (successCallback !== null) {
            connectionSuccessMessages[id] = successCallback;
        }

        if (errorCallback !== null) {
            connectionErrorMessages[id] = errorCallback;
        }

        const data = JSON.stringify({
            id: id,
            method: method,
            params: params
        });

        console.log('Client send:', data);

        connection.send(data);
    };

    connectionSuccessMessages.updateMatrix = function (result, that) {
        that.matrix = result.matrix;
    };

    connectionSuccessMessages.showWinner = function (result) {
        alert('Player id#' + result.winnerPlayerId + ' won this game!');
    };

    export default {
        name: 'Sudoku',
        props: {
            size: {
                type: [Number, String],
                require: true
            },
            playerId: {
                type: [Number, String],
                require: true
            },
            accessToken: {
                type: [String],
                require: true
            },
            gameId: {
                type: [Number, String],
                require: true
            },
            connectionUrl: {
                type: String,
                require: true
            }
        },
        data() {
            return {
                messages: [],
                matrix: [],
                cells: {
                    filled: 0,
                    empty: 0
                }
            }
        },
        created() {
            let that = this;

            connection = new WebSocket(this.connectionUrl);

            connection.onopen = function () {
                console.log("Connection established!");
                that.play();
            };

            connection.onmessage = function (e) {
                let data = JSON.parse(e.data);

                console.log('Server response:', data);

                if (data.result) {
                    if (data.id && connectionSuccessMessages[data.id]) {
                        connectionSuccessMessages[data.id](data.result, that);
                    }

                    if (data.result.message) {
                        that.messages.push(data.result.message);
                    }
                }
                else if (data.error) {
                    console.warn('Server response error:', data.error.message, data.error.trace);

                    if (data.id && connectionErrorMessages[data.id]) {
                        connectionErrorMessages[data.id](data.error, that);
                    }
                }
                else {
                    console.warn('Server response error: Invalid response', e);
                }
            };
        },
        methods: {
            play() {
                let that = this;

                connectionSendMessage('authorize', [that.playerId, that.accessToken], function () {
                    connectionSendMessage(
                        'play',
                        [that.gameId],
                        function (result) {
                            that.matrix = result.matrix;
                            that.cells = result.cells;
                        }
                    )
                })
            },
            makeMove(cellId, value) {
                let that = this;

                connectionSendMessage(
                    'makeAMove',
                    [that.gameId, cellId, parseInt(value)],
                    function (result) {
                        that.matrix = result.matrix;
                        that.cells = result.cells;
                    }
                )
            },
            validateValue(value) {
                return value.match(/^\d+$/)
            }
        }
    }
</script>

<style scoped>
    .sudoku-log {
        max-height: 300px;
        overflow-y: scroll;
    }

    .wrapper-sudoku * {
        margin: 0;
        padding: 0;
    }

    .wrapper-sudoku input:focus,
    .wrapper-sudoku select:focus,
    .wrapper-sudoku textarea:focus,
    .wrapper-sudoku button:focus {
        outline: none;
    }

    .wrapper-sudoku .buttons-container {
        display: grid;
        grid-template-rows: auto auto;
    }

    .wrapper-sudoku .button {
        display: inline-block;
        border-radius: 6px;
        background-color: whitesmoke;
        border: none;
        color: black;
        text-align: center;
        font-size: 16px;
        padding: 10px;
        width: 230px;
        transition: all 0.5s;
        cursor: pointer;
        margin: 0 0 25px 0;
        font-family: 'Dosis', sans-serif;
        font-weight: bold;
    }

    .wrapper-sudoku .button span {
        cursor: pointer;
        display: inline-block;
        position: relative;
        transition: 0.5s;
    }

    .wrapper-sudoku .button span:after {
        content: '\00bb';
        position: absolute;
        opacity: 0;
        top: 0;
        right: -20px;
        transition: 0.5s;
    }

    .wrapper-sudoku .button:hover span {
        padding-right: 25px;
    }

    .wrapper-sudoku .button:hover span:after {
        opacity: 1;
        right: 0;
    }

    .wrapper-sudoku .grid-sudoku {
        display: table;
        background: white;
        border: 3px solid black;
    }

    .wrapper-sudoku .grid-sudoku-16 .grid-cell {
        padding: 5px;
    }

    .wrapper-sudoku .grid-sudoku-9 .grid-cell-editor {
        font-size: 22px;
    }

    .wrapper-sudoku .grid-sudoku > div:nth-child(3), .grid-sudoku > div:nth-child(6) {
        border-bottom: 3px solid black;
    }

    .wrapper-sudoku .grid-row > div:nth-child(3), .grid-row > div:nth-child(6) {
        border-right: 3px solid black;
    }

    .wrapper-sudoku .grid-sudoku-4 > div:nth-child(3) {
        border-bottom: 1px solid black;
    }

    .wrapper-sudoku .grid-sudoku-4 .grid-row > div:nth-child(3) {
        border-right: 1px solid black;
    }

    .wrapper-sudoku .grid-cell {
        display: table-cell;
        padding: 10px;
        border: 1px solid gray;
    }

    .wrapper-sudoku .grid-cell-error {
        background-color: #f7cfd6;
    }

    .wrapper-sudoku .grid-cell-error .grid-cell-editor {
        background-color: #f7cfd6;
    }

    .wrapper-sudoku .grid-cell-editor {
        border: none;
        width: 20px;
        height: 20px;
        font-family: 'Dosis', sans-serif;
        font-weight: bold;
        text-align: center;
        font-size: 18px;
        transition: all ease 1.0s;
    }

    .wrapper-sudoku .answer {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        display: flex;
        justify-content: center;
        align-items: center;
    }
</style>