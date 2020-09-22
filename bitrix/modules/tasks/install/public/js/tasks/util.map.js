{"version":3,"sources":["util.js"],"names":["BX","namespace","mergeEx","Tasks","Util","formatTimeAmount","time","format","parseInt","isNaN","sign","Math","abs","hours","floor","minutes","seconds","nPad","num","substring","length","result","delay","action","actionCancel","ctx","DoNothing","this","timer","f","args","arguments","setTimeout","apply","cancel","clearTimeout","showByClass","node","hasClass","removeClass","hideByClass","addClass","fadeToggleByClass","duration","onComplete","animateShowHide","toShow","opacity","toHide","complete","fadeSlideToggleByClass","height","getInvisibleSize","fadeSlideHToggleByClass","width","params","type","isElementNode","p","Promise","reject","invisible","way","resolve","animate","start","finish","style","cssText","isFunction","call","step","state","rt","Runtime","animations","anim","k","easing","transition","transitions","linear","splice","fulfill","push","stop","pos","isEnter","e","getKeyFromEvent","isEsc","window","event","keyCode","which","filterFocusBlur","cbFocus","cbBlur","timeout","focus","eventArgs","bind","bindInstantChange","cb","value","debounce","toString","disable","setAttribute","enable","removeAttribute","getMessagePlural","n","msgId","pluralForm","langId","message","isArray","fireGlobalTaskEvent","taskData","options","taskDataUgly","task","taskUgly","onCustomEvent","top","slice","document","querySelectorAll","forEach","iframe","contentWindow","hintManager","bindHelp","scope","target","className","bindDelegate","passCtx","onHelpShow","onHelpHide","showDisposable","body","id","parameters","isPlainObject","closeLabel","autoHide","show","callback","util","hashCode","random","hintPopup","popup","wasDisposed","content","isNotEmptyString","title","create","attrs","text","htmlspecialchars","replace","html","margin","children","props","href","events","click","hide","PopupWindowManager","closeByEsc","closeIcon","angle","offsetLeft","offsetTop","onPopupClose","delegate","onViewModeHintClose","close","userOptions","save","disableSeveral","pack","enabled","data","innerHTML","PopupWindow","lightShadow","darkMode","bindOptions","position","zIndex","destroy","helpWindow","setAngle","offset","MouseTracker","coords","x","y","pageX","clientX","documentElement","scrollLeft","clientLeft","pageY","clientY","scrollTop","clientTop","getCoordinates","clone","getInstance","mouseTracker"],"mappings":"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","file":"util.map.js"}