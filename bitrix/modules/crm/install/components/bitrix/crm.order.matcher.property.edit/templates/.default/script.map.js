{"version":3,"sources":["script.js"],"names":["BX","namespace","Config","Order","Property","options","this","params","signedParamsString","actionRequestUrl","bindEvents","prototype","getFileIds","fileIds","form","el","input","type","isDomNode","index","i","elements","length","disabled","toLowerCase","previousSibling","name","p","indexOf","substring","isPlainObject","ID","value","reloadAction","that","fadeForm","ajax","submitAjax","url","method","dataType","data","action","isAjax","FILE_IDS","onsuccess","result","html","processed","processHTML","innerHTML","HTML","processScripts","SCRIPT","error","window","top","SidePanel","Instance","postMessage","property","showForm","saveAction","IFRAME","close","isNotEmptyString","redirect","document","location","href","applyAction","prepareForm","style","opacity","closeSlider","saveClickHandler","event","LOAD_FROM_REQUEST","parseInt","PROPERTY_ID","bind","proxy"],"mappings":"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","file":""}