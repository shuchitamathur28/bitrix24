insert  into b_workflow_status values (1, sysdate, 300, 'Y', 'Published', null, 'Y', 'N')
/
insert  into b_workflow_status values (2, sysdate, 100, 'Y', 'Draft', null, 'N', 'N')
/
insert  into b_workflow_status values (3, sysdate, 200, 'Y', 'Ready', null, 'N', 'Y')
/
DROP SEQUENCE SQ_B_WORKFLOW_STATUS
/
CREATE SEQUENCE SQ_B_WORKFLOW_STATUS START WITH 4 INCREMENT BY 1 NOMINVALUE NOMAXVALUE NOCYCLE NOCACHE NOORDER
/
