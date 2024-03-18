

<template>
    <div class="button-container" >
        <button type="button" @click="testData" class="btn btn-primary" style="margin: 0 5px 10px 0 ">test data</button>
        <button type="button" @click="addNode" class="btn btn-primary" style="margin: 0 5px 10px 0 ">Thêm node</button>
        <button type="button" v-if="selectedNodes.length == 2" @click="addConnector"  class="btn btn-primary" style="margin: 0 5px 10px 0 ">Thêm liên kết</button>
        <button type="button" v-if="selectedNodes" @click="openConnectorModal" class="btn btn-warning" style="margin: 0 5px 10px 0">Chỉnh sửa liên kết</button>
        <button type="button" v-if="selectedConnectorId" @click="removeSelectedConnector" class="btn btn-danger" style="margin: 0 5px 10px 0">Xóa liên kết</button>
        <button type="button" v-if="selectNodeId" @click="openModal" class="btn btn-warning" style="margin: 0 5px 10px 0">Chỉnh sửa node</button>
        <button type="button" v-if="selectNodeId" @click="removeSelected" class="btn btn-danger" style="margin: 0 5px 10px 0">Xóa node</button>


        <button type="button" @click="save" class="btn btn-primary" style="margin: 0 5px 10px 0 ; float: inline-end;">Lưu thay đổi</button>
    </div>
    <!-- <button @click="addNode" type="button" class="btn btn-primary">Thêm node</button>
    <button @click="addConnector" type="button" class="btn btn-primary">Thêm liên kết</button>
    <button @click="addConnector" style="display:end" type="button" class="btn btn-primary">Lưu thay đổi</button> -->
    <ejs-diagram
        :width="width"
        :height="height"
        :nodes="nodes"
        :layout="layout"
        :connectors="connectors"
        :allowDragAndDrop='true'
        @positionChange="onPositionChange"
        @selectionChange="onSelectionChange"
    >
    </ejs-diagram>


    <!-- Modal -->
    <div class="modal fade" id="nodeModal" tabindex="-1" aria-labelledby="nodeModalLabel" aria-hidden="true" v-show="showPopup">
         <div class="modal-dialog nodeModal-dialog-centered">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalToggleLabel">Modal 1</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="exampleInputEmail1" class="form-label">ID</label>
                    <input type="text" class="form-control" id="exampleInputEmail1" v-model="nodeId" aria-describedby="emailHelp">
                </div>
                <div class="mb-3">
                    <label for="exampleInputEmail1" class="form-label">Tên</label>
                    <input type="text" class="form-control" id="exampleInputEmail1" v-model="nodeContent" aria-describedby="emailHelp">
                </div>
                <div class="mb-3">
                    <label for="exampleInputPassword1" class="form-label">Bộ phận đảm nhận</label>
                    <select class="form-select form-select" aria-label=".form-select-lg example" v-model="nodeDepartment">
                        <option disabled value="">Open this select menu</option>
                        <option v-for="value in departments" :value="value.code" :key="value.code">{{ value.name }}</option>

                    </select>
                </div>

                 <div class="mb-3">
                    <label for="exampleInputEmail1" class="form-label">bước tiêp theo</label>
                    <div>
                        <multi-list-select
                            :list="options"
                            option-value="id"
                            option-text="name"
                            :selected-items="selectedOptions"
                            @select="onchangeSelect"
                        >
                        </multi-list-select>
                    </div>

                </div>
                <div class="mb-3">
                    <div class="flex-result">
                        <table class="ui celled table">
                            <thead>
                                <tr>
                                    <th>id</th>
                                    <th>Tên</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody v-for="item in selectedOptions" :key="item.code">

                            <tr>
                                <td>{{item.id}}</td>
                                <td>{{item.name}}</td>
                                <td>
                                    <textarea
                                        :name="`status[${item.id}]`"
                                        type="text"
                                        style="width: 200px"
                                        class="form-control"
                                        v-model="item.next_step"
                                        placeholder="Nhập điều kiện hoàn thành!"
                                    />
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" @click="submitNodeEdit" class="btn btn-primary">Cập nhật</button>
            </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { CustomCursorPlugin, DiagramComponent } from "@syncfusion/ej2-vue-diagrams";
    import { MultiListSelect } from "vue-search-select"
    import 'vue-search-select/dist/VueSearchSelect.css';
export default{
    props: {
        'data': Object,
        'departments': Object,
    },
    components: {
        'ejs-diagram': DiagramComponent,
         'multi-list-select': MultiListSelect
    },
    data() {
        return {
            selectNodeId: null,
            width: '100%',
            height: '600px',
            layout: {
                type: "OrganizationalChart",
                },
            nodes: [],
            connectors: [],
            showPopup: false,
            showConnectorPopup: false,
            selectedNode: null,
            nodeDetail : [],
            nodeContent: "",
            nodeId:"",
            nodeDepartment: "",
            nodeNextStep: [],
            selectedOptions: [],
            statusDepartment: null,
            options: [],
            selectedNodes: [], // Mảng để lưu trữ ID của nodes được chọn
            selectedConnectorId: null, //lưu id connector khi chọn
        }
    },
    mounted () {
        const nodesProps = this.data.map(item => {
            console.log('item.next_step',item.next_step)
            return {
                id: item.code,
                annotations: [{
                    content: item.name,
                    parentId : item.department_code,
                    nextStep : item.next_step
                }],
                width: 150,
                height: 60,
                offsetX: item.location.offsetX,
                offsetY: item.location.offsetY
            };
        });
        const connectorsHandle = this.data.map(item => {
            let dataConnect = [];
            if(item.next_step != null){
                let dataNextStep = item.next_step;
                const keys = Object.keys(dataNextStep);
                keys.forEach( element => {
                    dataConnect.push(
                        {
                            id: 'Connector' + item.code,
                            annotations: [{
                                content: 'tesst',
                            }],
                            targetID: element,
                            sourceID: item.code,
                            style: {
                                strokeColor: 'red',
                                strokeWidth: 2,
                            },
                            targetDecorator: {
                            style: {
                                    fill: 'red',
                                    strokeColor: 'red'
                                }
                            },
                        }
                    )
                });
            }
            return dataConnect;
        });
        // Làm phẳng và lọc mảng
        const flattenedArray = connectorsHandle.flat().filter(item => item && Object.keys(item).length);


        // Chuyển đổi mảng
        const connectorsProps = flattenedArray.map((item, index) => {
            return {
                id: "Connector" + (24 + index),
                sourceID: item.sourceID || "",
                targetID: item.targetID,
                style: {
                    strokeColor: "red",
                    strokeWidth: 2
                },
                targetDecorator: {
                    style: {
                        fill: "red",
                        strokeColor: "red"
                    }
                }
            };
        });
        this.nodes = [...this.nodes, ...nodesProps];
        this.connectors = [...this.connectors, ...connectorsProps];

    },
    methods: {
        ////////////////////////////////////////////////////////////////////// xử lý sự kiện thêm nodes //////////////////////////////////////////////////////////////////////////////////
        addNode() {
            const newNode = {
                id: `Node${this.nodes.length + 1}`,
                annotations: [{
                    content: 'Text',
                    parentId : '',
                    nextStep : ''
                }],
                width: 150,
                height: 60,
                offsetX: 200,
                offsetY: 200
            };
            this.nodes = [...this.nodes, newNode];
            this.connectors = [...this.connectors];
        },
        ////////////////////////////////////////////////////////////////////// xử lý sự kiện thêm nodes //////////////////////////////////////////////////////////////////////////////////


        ////////////////////////////////////////////////////////////////////// xử lý sự kiện thêm connector //////////////////////////////////////////////////////////////////////////////////
        addConnector() {
            if (this.selectedNodes.length === 2) {
                this.createConnector(this.selectedNodes[0], this.selectedNodes[1]);
            } else {
                this.message('warning' ,'Vui lòng chọn đúng 2 nodes để tạo liên kết.', 'Cảnh báo');
            }
        },

        createConnector(sourceId, targetId) { // tạo 1 connector
            const newConnector = {
                id: `Connector${this.connectors.length + 1}`,
                sourceID: sourceId,
                targetID: targetId,
                type: 'Straight',
                style: {
                    strokeWidth: 2,
                    strokeColor: 'red'
                },
                targetDecorator: {
                    style: {
                        fill: "red",
                        strokeColor: "red"
                    }
                }
            };
            this.connectors = [...this.connectors, newConnector];
        },
        ////////////////////////////////////////////////////////////////////// xử lý sự kiện thêm connector //////////////////////////////////////////////////////////////////////////////////

        ////////////////////////////////////////////////////////////////////// xử lý sự kiện click //////////////////////////////////////////////////////////////////////////////////
            onSelectionChange(event) { //xử lý sự kiện click trong node
                // Xử lý sự kiện bỏ chọn
                if (event.state === "Changing" && event.type === "Removal") {
                    this.resetSelection();
                    return;
                }

                // Xác định đối tượng được chọn
                const selectedItem = event.newValue[0];
                if (!selectedItem) {
                    return;
                }

                // Xử lý chọn node
                if (selectedItem.propName === "nodes") {
                    this.handleNodeSelection(selectedItem.properties.id);
                }
                // Xử lý chọn connector
                else if (selectedItem.propName === "connectors") {
                    this.handleConnectorSelection(selectedItem.properties.id);
                }
            },

            resetSelection() {
                this.selectNodeId = null;
                this.selectedConnectorId = null;
                this.selectedNodes = [];
            },

            handleNodeSelection(nodeId) {
                this.selectNodeId = nodeId;
                if (!this.selectedNodes.includes(nodeId)) {
                    this.selectedNodes.push(nodeId);
                }
            },

            handleConnectorSelection(connectorId) {
                this.selectedConnectorId = connectorId;
            },
        ////////////////////////////////////////////////////////////////////// xử lý sự kiện click //////////////////////////////////////////////////////////////////////////////////

        ////////////////////////////////////////////////////////////////////// xử lý sự kiện kéo thả //////////////////////////////////////////////////////////////////////////////////
        onPositionChange(event) { // xử lý sự kiện kéo thả lấy vị trí
            if(event.state === "Completed"){
                let foundObject = this.nodes.find(node => node.id === this.selectNodeId);
                    if (foundObject) {
                        foundObject.offsetX = event.newValue.offsetX;
                        foundObject.offsetY = event.newValue.offsetY;
                    }
                this.nodes = [...this.nodes];
                this.connectors = [...this.connectors];
            }
        },
        ////////////////////////////////////////////////////////////////////// xử lý sự kiện kéo thả //////////////////////////////////////////////////////////////////////////////////

        ////////////////////////////////////////////////////////////////////// xử lý sự kiện submit form //////////////////////////////////////////////////////////////////////////////////
        submitNodeEdit() {
            let foundObject = this.nodes.find(node => node.id === this.selectNodeId);
            if (foundObject) {
                // Lưu trữ ID cũ trước khi thay đổi
                const oldId = foundObject.id;
                // Cập nhật thuộc tính của đối tượng
                foundObject.annotations[0].nextStep = this.nodeNextStep;
                foundObject.annotations[0].parentId = this.nodeDepartment;
                foundObject.annotations[0].content = this.nodeContent;
                this.nodeDetail.id = this.nodeId;

                // Cập nhật các connector có liên quan đến node này
                this.connectors.forEach(connector => {
                    if (connector.sourceID === oldId) {
                        connector.sourceID = this.nodeId;
                    }
                    if (connector.targetID === oldId) {
                        connector.targetID = this.nodeId;
                    }
                });
            }
            this.nodes = [...this.nodes];
            this.connectors = [...this.connectors];
            this.closePopup();
        },
        ////////////////////////////////////////////////////////////////////// xử lý sự kiện submit form //////////////////////////////////////////////////////////////////////////////////

        //////////////////////////////////////////////////////////////////////< xử lý sự kiện đóng mở popup >//////////////////////////////////////////////////////////////////////////////////
         openModal() {
            this.showPopup = true;
            this.nodeDetail = this.nodes.find(node => node.id === this.selectNodeId);
            this.nodeContent = this.nodeDetail.annotations[0].content;
            this.nodeDepartment = this.nodeDetail.annotations[0].parentId;
            this.nodeId = this.nodeDetail.id;



            // this.selectedOptions = this.nodeDetail.annotations[0].next_step;
            // console.log(this.nodeDetail.annotations[0].nextStep);
            // this.selectedOptions = JSON.stringify(this.nodeDetail.annotations[0].nextStep);
            this.options = this.nodes.map(item => {
                let annotation = item.annotations[0];
                return {
                    name: annotation.content,
                    id: item.id
                };
            });


            const options = new Map(this.options.map(item => [item.id, item]));
            let dataConvert = [];
            for (const [key, value] of Object.entries(this.nodeDetail.annotations[0].nextStep)) {
                if (options.has(key)) {
                const item = { ...options.get(key) };
                item.next_step = Object.entries(value).map(([k, v]) => `${k}: ${v}`).join(', ');
                dataConvert.push(item);
                }
            }
            this.selectedOptions = dataConvert;

            new bootstrap.Modal(document.getElementById('nodeModal')).show();
        },
        closePopup() {
            this.showPopup = false;
            new bootstrap.Modal(document.getElementById('nodeModal')).hide();
            document.querySelector('.modal-backdrop').remove();
        },
        //////////////////////////////////////////////////////////////////////< xử lý sự kiện đóng mở popup />//////////////////////////////////////////////////////////////////////////////////

        ////////////////////////////////////////////////////////////////////// xử lý sự kiện xóa node và connector //////////////////////////////////////////////////////////////////////////////////
        removeSelected() {
            if (this.selectNodeId) {
                // Xóa node được chọn
                this.nodes = this.nodes.filter(node => node.id !== this.selectNodeId);
                // Xóa các connector liên quan đến node
                this.connectors = this.connectors.filter(connector => connector.sourceID !== this.selectNodeId && connector.targetID !== this.selectNodeId);
                // Đặt lại ID node được chọn
                this.selectNodeId = null;
            }
        },
        removeSelectedConnector() {
            if (this.selectedConnectorId) {
                this.connectors = this.connectors.filter(connector => connector.id !== this.selectedConnectorId);
                this.selectedConnectorId = null;
            } else {
                console.log("Không có connector nào được chọn để xóa.");
            }
        },
        ////////////////////////////////////////////////////////////////////// xử lý sự kiện xóa node và connector //////////////////////////////////////////////////////////////////////////////////

        ////////////////////////////////////////////////////////////////////// xử lý message //////////////////////////////////////////////////////////////////////////////////
        message(type ,message, title) {
            toastr.clear()

            toastr.options = {
                closeButton: true,
                positionClass: 'toast-bottom-right',
                showDuration: 1000,
                hideDuration: 1000,
                timeOut: 60000,
                extendedTimeOut: 1000,
                showEasing: 'swing',
                hideEasing: 'linear',
                showMethod: 'fadeIn',
                hideMethod: 'fadeOut',
            }
            toastr[type](message, title);
        },
        ////////////////////////////////////////////////////////////////////// xử lý message //////////////////////////////////////////////////////////////////////////////////

        onchangeSelect(selectedItem) {
            // Kiểm tra xem selectedItem có tồn tại và có id không
            if (selectedItem) {
                // Xử lý trường hợp name rỗng
                this.selectedOptions = [];
                this.selectedOptions.push(...selectedItem);
            } else {
                console.log("Mục được chọn không hợp lệ");
            }
        },

        save(){
            console.log('save new node');
            console.log(this.nodes)
        },

        testData() {
            console.log('options:', this.selectedOptions, this.options);
        },
        submitConditions() {
            const result = this.selectedItems.reduce((acc, item) => {
                acc[item.id] = { ...acc[item.id], [item.id]: item.condition };
                return acc;
            }, {});

            console.log(result);
            // Làm gì đó với kết quả...
        }
    }
};
</script>
<style scoped>
    .button-container {
        justify-items: end;
    }

    .button{
        margin: 5px 0 5px 5px;
    }
    .modal {
        /* Style cho background modal */
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .modal-content {
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .close {
        float: right;
        font-size: 1.5rem;
        cursor: pointer;
    }

</style>


<style>
    .modal-backdrop {
        z-index: 1040 !important;
    }

    .modal {
        z-index: 1050 !important;
    }
</style>
