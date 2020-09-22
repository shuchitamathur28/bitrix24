{"version":3,"sources":["logic.js"],"names":["BX","namespace","Tasks","Component","TasksTaskTemplate","extend","sys","code","methods","construct","this","callConstruct","initFileView","query","Util","Query","url","checkListChanged","showCloseConfirmation","analyticsData","bindEvents","self","Dispatcher","bindEvent","id","onButtonClick","bind","option","edit","addCustomEvent","proxy","syncTags","bindControl","passCtx","onImportantButtonClick","onSaveButtonClick","onCancelButtonClick","createTemplateMenu","errors","alert","then","reload","event","CheckListInstance","checkListSlider","optionManager","slider","getSlider","denyAction","popup","PopupWindow","titleBar","message","content","closeIcon","buttons","PopupWindowButton","text","className","events","click","close","onPopupClose","destroy","show","Event","EventEmitter","subscribe","eventData","action","data","allowedActions","util","in_array","toggleFooterWrap","node","priority","PRIORITY","newPriority","callRemote","ID","result","isSuccess","toggleClass","tags","length","tmpTags","each","tag","push","NAME","name","SE_TAG","UI","Notification","Center","notify","window","location","controlAll","area","top","viewElementBind","type","isElementNode","getAttribute","isSaving","activateLoading","saveCheckList","treeStructure","getTreeStructure","args","items","getRequestData","templateId","userId","USER_ID","params","Object","assign","checklistCount","getDescendantsCount","run","traversedItems","getData","TRAVERSED_ITEMS","keys","forEach","nodeId","item","findChild","fields","getId","setId","saveStableTreeStructure","deactivateLoading","execute","rerender","footer","saveButton","classWait","classActive","hasClass","addClass","removeClass","menuItemsList","delimiter","tabId","onclick","getMenuWindow","layout","setShowCompleted","getShowCompleted","handleTaskOptions","setShowOnlyMine","getShowOnlyMine","menu","PopupMenu","create","closeByEsc","offsetLeft","getBoundingClientRect","width","angle","popupWindow","call"],"mappings":"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","file":"logic.map.js"}