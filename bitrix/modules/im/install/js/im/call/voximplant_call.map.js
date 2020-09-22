{"version":3,"sources":["voximplant_call.js"],"names":["debug","BX","namespace","ajaxActions","invite","cancel","answer","decline","hangup","ping","clientEvents","voiceStarted","voiceStopped","pingPeriod","navigator","mediaParams","audio","video","mandatory","chromeMediaSource","maxWidth","screen","width","maxHeight","height","optional","googTemporalLayeredScreencast","mediaDevices","getUserMedia","Call","VoximplantCall","config","PlainCall","superclass","constructor","apply","this","arguments","window","VoxImplant","Error","voximplant","voximplantCall","signaling","Signaling","call","peers","voiceDetection","screenShared","deviceList","__onMicAccessResultHandler","__onMicAccessResult","bind","__onLocalDevicesUpdatedHandler","__onLocalDevicesUpdated","__onLocalMediaRendererAddedHandler","__onLocalMediaRendererAdded","__onBeforeLocalMediaRendererRemovedHandler","__onBeforeLocalMediaRendererRemoved","init","pingInterval","setInterval","extend","AbstractCall","prototype","users","forEach","userId","createPeer","sendPing","Peer","ready","initiatorId","onStreamReceived","e","runCallback","Event","onStreamRemoved","onStateChanged","__onPeerStateChanged","getUsers","result","calculatedState","getClient","self","Promise","resolve","reject","Voximplant","then","client","enableSilentLogging","setLoggerCallback","log","label","message","bindClientEvents","catch","err","addEventListener","Events","MicAccessResult","streamManager","Hardware","StreamManager","get","on","HardwareEvents","DevicesUpdated","MediaRendererAdded","BeforeMediaRendererRemoved","removeClientEvents","removeEventListener","off","setMuted","muted","muteMicrophone","unmuteMicrophone","setVideoEnabled","videoEnabled","sendVideo","setCameraId","cameraId","CameraManager","setCallVideoSettings","setMicrophoneId","microphoneId","AudioDeviceManager","setCallAudioSettings","inputId","startScreenSharing","shareScreen","stopScreenSharing","stopSharingScreen","onLocalMediaStopped","tag","isScreenSharingStarted","inviteUsers","type","isPlainObject","isArray","attachToConference","userIds","response","i","length","onUserInvited","onInvited","error","onCallFailure","useVideo","sendAnswer","ajax","runAction","data","callId","id","callInstanceId","instanceId","destroy","code","reason","sendHangup","state","_config","experiments","callConference","number","receiveVideo","customData","bindCallEvents","onCallConnected","CallEvents","Connected","Failed","onCallFailed","enumerateDevices","onDeviceListUpdated","hasOwnProperty","status","name","Disconnected","__onCallDisconnected","MessageReceived","__onCallMessageReceived","EndpointAdded","__onCallEndpointAdded","isAnyoneParticipating","isParticipating","attachVoiceDetection","stream","SimpleVAD","mediaStream","onVoiceStarted","onLocalVoiceStarted","onVoiceStopped","onLocalVoiceStopped","sendVoiceStarted","sendVoiceStopped","onUserStateChanged","UserState","__onPullEvent","command","params","extra","handlers","Call::answer","__onPullEventAnswer","Call::hangup","__onPullEventHangup","Call::usersInvited","__onPullEventUsersInvited","Call::finish","__onPullEventFinish","senderId","__onPullEventAnswerSelf","setReady","setDeclined","Idle","onLocalMediaReceived","renderer","kind","endpoint","userName","isNotEmptyString","substr","parseInt","setEndpoint","EndpointEvents","InfoUpdated","JSON","parse","text","eventName","onUserVoiceStarted","onUserVoiceStopped","clearInterval","onDestroy","__runAjaxAction","sendCancel","__sendMessage","retransmit","requestId","Engine","getInstance","getUuidv4","sendMessage","stringify","signalName","calling","declined","inviteTimeout","tracks","sharing","callingTimeout","callbacks","isFunction","DoNothing","__onEndpointRemoteMediaAddedHandler","__onEndpointRemoteMediaAdded","__onEndpointRemoteMediaRemovedHandler","__onEndpointRemoteMediaRemoved","__onEndpointRemovedHandler","__onEndpointRemoved","calculateState","clearTimeout","updateCalculatedState","mediaRenderers","addMediaRenderer","element","remove","bindEndpointEventHandlers","mediaRenderer","MediaStream","getTracks","track","updateMediaStream","hasTrack","removeTrack","getTrackById","addTrack","localTrackId","RemoteMediaAdded","RemoteMediaRemoved","Removed","removeEndpointEventHandlers","Connecting","Calling","Declined","Ready","previousState","setTimeout","onInviteTimeout"],"mappings":"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","file":"voximplant_call.map.js"}