USE material_planning_dev;

INSERT INTO master_customers (id_customer, name_customer) VALUES
 ('CUST-001','PT Contoh Steel'),
 ('CUST-002','PT Maju Jaya')
ON DUPLICATE KEY UPDATE name_customer=VALUES(name_customer);

INSERT INTO tr_spk_marketing (id_spkmarketing, no_surat, id_customer) VALUES
 ('P2600001','001/MP/SPK/IX/2026','CUST-001'),
 ('P2600002','002/MP/SPK/IX/2026','CUST-002')
ON DUPLICATE KEY UPDATE no_surat=VALUES(no_surat);

REPLACE INTO view_material_planning
 (id_dt_spkmarketing,no_spk,customer,id_material,no_alloy,thickness,width,length_val,delivery_date,total_weight,total_sheet,fg_booking,status_lanjutan) VALUES
 ('P2600001-1','001/MP/SPK/IX/2026','PT Contoh Steel','SUS304-2B-0.8','ALLOY-001',0.8,1219,2438,'2026-09-20',5000,200,1200.5,'1'),
 ('P2600001-2','001/MP/SPK/IX/2026','PT Contoh Steel','SUS430-BA-1.0','ALLOY-002',1.0,1000,2000,'2026-09-22',3200,150,0,'1'),
 ('P2600002-1','002/MP/SPK/IX/2026','PT Maju Jaya','SPCC-SD-1.2','ALLOY-003',1.2,914,1828,'2026-09-25',7500,300,7500,'2');

REPLACE INTO dt_spkmarketing
 (id_dt_spkmarketing,id_spkmarketing,id_material,width,total_weight,status_lanjutan,status_close_planning,approved,deal) VALUES
 ('P2600001-1','P2600001','SUS304-2B-0.8',1219,5000,'1','OPN','1','1'),
 ('P2600001-2','P2600001','SUS430-BA-1.0',1000,3200,'1','OPN','1','1'),
 ('P2600002-1','P2600002','SPCC-SD-1.2',914,7500,'2','OPN','1','1');

REPLACE INTO stock_material
 (id_stock,id_category3,lotno,width,qty,weight,totalweight,booking,delivery_book,sisa_spk,qty_sheet,customer,id_gudang) VALUES
 ('STK-WIP-001','SUS304-2B-0.8','LOT-WIP-001',1219,2,1500,3000,500,0,2500,0,'', '1'),
 ('STK-FG-001','SUS304-2B-0.8','LOT-FG-001',1219,5,240,1200,0,0,1200,10,'PT Contoh Steel','3');

INSERT INTO dt_trans_pr (idmaterial,totalweight) VALUES ('SUS304-2B-0.8',8000);
INSERT INTO dt_incoming (id_material,width_recive) VALUES ('SUS304-2B-0.8',3000);
