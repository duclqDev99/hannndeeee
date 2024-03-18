export const PRODUCT_MODEL = "Botble\\Ecommerce\\Models\\Product";
export const BATCH_MODEL = "Botble\\WarehouseFinishedProducts\\Models\\ProductBatch";
export const SHOWROOM_WAREHOUSE_MODEL = "Botble\\Showroom\\Models\\ShowroomWarehouse";
export const SALE_WAREHOUSE_CHILD_MODEL = "Botble\\SaleWarehouse\\Models\\SaleWarehouseChild";
export const HUBWAREHOUSE_MODEL = "Botble\\HubWarehouse\\Models\\Warehouse";


export const playSound = (fileName) => {
    if (!fileName) return;
    const audio = document.createElement('audio');
    audio.src = `/storage/scan-audio/${fileName}.mp3`;
    audio?.play();
    audio?.remove();
}
