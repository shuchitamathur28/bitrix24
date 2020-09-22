{"version":3,"sources":["script.js"],"names":["BX","namespace","gridId","Voximplant","Lines","init","bind","this","addGroup","reloadGrid","Main","gridManager","gridData","getById","instance","reload","showConfig","configId","SidePanel","Instance","open","cacheable","allowChangeHistory","events","onClose","editor","NumberGroup","show","deleteGroup","id","showLoader","ajax","runComponentAction","data","then","response","hideLoader","catch","error","errors","alert","message","editGroup","groupId","addNumberToGroup","number","removeNumberFromGroup","getCallerIdFields","Promise","resolve","reject","runAction","phoneNumber","verifyCallerId","configPromise","callerIdForm","CallerIdSlider","dataPromise","deleteCallerId","self","confirm","replace","result","deleteSip","deleteNumber","NumberRent","create","cancelNumberDeletion","config","slider","groupName","selectedNumbers","unassignedNumbers","elements","numbersContainer","createButton","cancelButton","callBacks","type","isFunction","DoNothing","prototype","fetchUnassignedNumbers","e","console","width","onSliderClose","onDestroy","onSliderDestroy","contentCallback","promise","top","loadExt","layout","render","createFragment","props","className","children","text","attrs","value","placeholder","change","currentTarget","renderUnassignedNumbers","click","onCreateButtonClick","onCancelButtonClick","map","numberFields","onNumberChanged","for","showError","adjust","hideError","cleanNode","destroy","checked","addClass","name","numbers","Object","keys","removeClass","close"],"mappings":"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","file":"script.map.js"}