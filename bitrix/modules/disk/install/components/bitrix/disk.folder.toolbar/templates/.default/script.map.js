{"version":3,"file":"script.min.js","sources":["script.js"],"names":["BX","namespace","Disk","FolderToolbarClass","parameters","this","id","destFormName","toolbarContainer","createBlankFileUrl","renameBlankFileUrl","targetFolderId","defaultService","defaultServiceLabel","ajaxUrl","setEvents","prototype","emptyTrashCan","onCustomEvent","createFolder","modalWindow","modalId","title","message","contentClassName","contentStyle","paddingTop","paddingBottom","events","onAfterPopupShow","focus","onPopupClose","destroy","content","create","props","className","for","children","text","type","value","style","fontSize","marginTop","buttons","PopupWindowButton","click","delegate","newName","ajax","method","dataType","url","addToLinkParam","data","name","onsuccess","status","document","location","getUrlToShowObjectInGrid","folder","showModalWithStatusAction","PopupWindowManager","getCurrentPopup","close","removeShared","params","entityId","item","entityName","entityAvatar","avatar","entityToNewShared","child","findChild","attribute","data-dest-id","remove","canForwardNewShared","appendNewShared","right","createExtendedFolder","createFile","PopupMenu","cursor","html","e","openMenuWithServices","PreventDefault","runCreatingFile","targetElement","obElementViewer","CViewer","show","href","onclick","isEnableLocalEditInDesktop","setEditService","adjust","helpDiskDialog","getNameEditService","angle","position","offset","autoHide","overlay","opacity","blockFeatures","closeIcon","zIndex","documentType","createDoc","blankDocument","createBlankElementByParams","docType","editUrl","renameUrl","setCurrent","afterSuccessCreate","response","isLocalEditService","initEditService","window","objectId","onbeforeunload","diskUnloadCreateDoc","setTimeout","runActionByCurrentElement"],"mappings":"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"}