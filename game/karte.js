	var http_request = false;
	var timer = new Array();
	
	function status(sector, status) {
    	statusmakeRequest(sector, 'xml.php?'+session+'&sector='+sector+'&status='+status, status);
	}
    
    function statusmakeRequest(sector, url, status) {

		http_request = false;

        if (window.XMLHttpRequest) { // Mozilla, Safari,...
            http_request = new XMLHttpRequest();
            if (http_request.overrideMimeType) {
                http_request.overrideMimeType('text/xml');
            }
        } else if (window.ActiveXObject) { // IE
            try {
                http_request = new ActiveXObject('Msxml2.XMLHTTP');
            } catch (e) {
                try {
                    http_request = new ActiveXObject('Microsoft.XMLHTTP');
                } catch (e) {}
            }
        }

        if (!http_request) {
            alert('XMLHTTP Instanz konnte nicht gestartet werden. Du scheinst einen alten Browser zu verwenden, oder du hast ActiveX ausgeschaltet.');
            return false;
        }
        http_request.onreadystatechange = function () {
        	
	        if (http_request.readyState == 4) {
    	        if (http_request.status == 200) {
        	    	
            	    var xmldoc = http_request.responseXML;
                	var status_node = xmldoc.getElementsByTagName('ow').item(0).getElementsByTagName('status').item(0);

                	var i=sector*500+1;
                	var e=sector*500+500-1;
                	while (i <= e) {
                		if (document.getElementById(i).className != 'kg2') {
                			document.getElementById(i).className = 'kg';
                		}
                		i++;
                	}
                	
                	var cls = 'ke';
                	
                	if (status == 1) { cls = 'kf'; }
                	if (status == 2) { cls = 'ke'; }
                	if (status == 3) { cls = 'kn'; }
                	if (status == 4) { cls = 'ky'; }
                	if (status == 5) { cls = 'ke'; }
                	if (status == 6) { cls = 'ke'; }
                	if (status == 7) { cls = 'ke'; }
                	
                	var j=0;
                	while (xmldoc.getElementsByTagName('ow').item(0).getElementsByTagName('ubl').item(j)) {
                		
                		var ubl = xmldoc.getElementsByTagName('ow').item(0).getElementsByTagName('ubl').item(j).firstChild.data;
                		document.getElementById(ubl).className = cls;
                		if (status == 6) { 
                			document.getElementById('i'+ubl).src = 'img/t.gif';
                		}
                		j++;
                		
                	}
                	
	            } else {
	            
    	            alert('There was a problem with the request.');
    	            
        	    }
        	}
       	
        }
        http_request.open('GET', url, true);
        http_request.send(null);

    }
    
    
    function t (ubl) {
    	tmakeRequest('xml.php?'+session+'&ubl='+ubl);
	}
    
    function tmakeRequest(url) {

        http_request = false;

        if (window.XMLHttpRequest) { // Mozilla, Safari,...
            http_request = new XMLHttpRequest();
            if (http_request.overrideMimeType) {
                http_request.overrideMimeType('text/xml');
            }
        } else if (window.ActiveXObject) { // IE
            try {
                http_request = new ActiveXObject('Msxml2.XMLHTTP');
            } catch (e) {
                try {
                    http_request = new ActiveXObject('Microsoft.XMLHTTP');
                } catch (e) {}
            }
        }

        if (!http_request) {
            alert('XMLHTTP Instanz konnte nicht gestartet werden. Du scheinst einen alten Browser zu verwenden, oder du hast ActiveX ausgeschaltet.');
            return false;
        }
        http_request.onreadystatechange = function () {
        	
	        if (http_request.readyState == 4) {
    	        if (http_request.status == 200) {
        	    	
            	    var xmldoc = http_request.responseXML;
                	var status_node = xmldoc.getElementsByTagName('ow').item(0).getElementsByTagName('status').item(0);

                	var name = '-';
                	var clan = '-';
                	var base = '-';
                	var ubl  = '-';
                	var dist = '-';
                	var tf_e = '-';
                	var tf_t = '-';
                	
                	if (status_node.firstChild.data == 1) {
                		
	                	var name = xmldoc.getElementsByTagName('ow').item(0).getElementsByTagName('name').item(0).firstChild.data;
	                	var base = xmldoc.getElementsByTagName('ow').item(0).getElementsByTagName('base').item(0).firstChild.data;
	                	var ubl  = xmldoc.getElementsByTagName('ow').item(0).getElementsByTagName('ubl').item(0).firstChild.data;
	                	var clan = xmldoc.getElementsByTagName('ow').item(0).getElementsByTagName('clan').item(0).firstChild.data;
	                	var dist = xmldoc.getElementsByTagName('ow').item(0).getElementsByTagName('dist').item(0).firstChild.data;
	                	var tf_e = xmldoc.getElementsByTagName('ow').item(0).getElementsByTagName('tf_e').item(0).firstChild.data;
	                	var tf_t = xmldoc.getElementsByTagName('ow').item(0).getElementsByTagName('tf_t').item(0).firstChild.data;
					    
                	} 
                	
                	if ((status_node.firstChild.data == 2)) {
                	
                		var dist = xmldoc.getElementsByTagName('ow').item(0).getElementsByTagName('dist').item(0).firstChild.data;
                	
                	}
                	
                	if ((status_node.firstChild.data == 99)) {
                	
                		alert('session ungueltig');
                	
                	}                	
                	
                	document.getElementById("t_ubl").innerHTML = ubl;
	            	document.getElementById("t_name").innerHTML = name;
	            	document.getElementById("t_clan").innerHTML = clan;
	            	document.getElementById("t_base").innerHTML = base;
	            	document.getElementById("t_entfernung").innerHTML = dist*2.5;
	            	document.getElementById("t_tf_eisen").innerHTML = tf_e;
	            	document.getElementById("t_tf_titan").innerHTML = tf_t;
	            	//document.getElementById("t_clanlink").href = "claninfo.php?" + "SID" + "&clan=" + id;
	            	document.getElementById("t_mission").href = "mission.php?" + session + "&to=" + ubl;
	            	document.getElementById("t_beschuss").href = "beschuss.php?" + session + "&to=" + ubl;
	            	document.getElementById("t_nachricht").href = "nachricht_schreiben.php?" + session + "&to=" + ubl;
	            	document.getElementById("t_profil").href = "profil.php?" + session + "&ubl=" + ubl;
	            	document.getElementById("t_neutral").href = "karte_neu.php?" + session + "&mark=10&to=" + ubl;
	            	document.getElementById("t_farm").href = "karte_neu.php?" + session + "&mark=1&to=" + ubl;
	            	document.getElementById("t_enemy").href = "karte_neu.php?" + session + "&mark=2&to=" + ubl;
            		document.getElementById("t_nap").href = "karte_neu.php?" + session + "&mark=3&to=" + ubl;
                	
	            } else {
	            
    	            alert('There was a problem with the request.');
    	            
        	    }
        	}
       	
        }

        document.getElementById('t_name').innerHTML = 'loading ...';
        document.getElementById('t_base').innerHTML = 'loading ...';
        document.getElementById('t_clan').innerHTML = 'loading ...';
        document.getElementById('t_ubl').innerHTML  = 'loading ...';
        document.getElementById('t_tf_eisen').innerHTML = 'loading ...';
        document.getElementById('t_tf_titan').innerHTML = 'loading ...';
        document.getElementById('t_entfernung').innerHTML = 'loading ...';    
        
        http_request.open('GET', url, true);
        http_request.send(null);

    }	
	
    function s (ubl) {
   		timer[ubl] = window.setTimeout("smakeRequest('xml.php?"+session+"&ubl="+ubl+"')", 200);
	}
	
	function k (ubl) {
   		window.clearTimeout(timer[ubl]);
	}
    
    function smakeRequest(url) {
        http_request = false;

        if (window.XMLHttpRequest) { // Mozilla, Safari,...
            http_request = new XMLHttpRequest();
            if (http_request.overrideMimeType) {
                http_request.overrideMimeType('text/xml');
            }
        } else if (window.ActiveXObject) { // IE
            try {
                http_request = new ActiveXObject('Msxml2.XMLHTTP');
            } catch (e) {
                try {
                    http_request = new ActiveXObject('Microsoft.XMLHTTP');
                } catch (e) {}
            }
        }

        if (!http_request) {
            alert('XMLHTTP Instanz konnte nicht gestartet werden. Du scheinst einen alten Browser zu verwenden, oder du hast ActiveX ausgeschaltet.');
            return false;
        }
        http_request.onreadystatechange = function () {
        	
	        if (http_request.readyState == 4) {
    	        if (http_request.status == 200) {
        	    	
            	    var xmldoc = http_request.responseXML;
                	var status_node = xmldoc.getElementsByTagName('ow').item(0).getElementsByTagName('status').item(0);

                	var name = '-';
                	var clan = '-';
                	var base = '-';
                	var ubl  = '-';
                	var dist = '-';
                	var tf_e = '-';
                	var tf_t = '-';
                	
                	
                	
                	if (status_node.firstChild.data == 1) {
                		
	                	var name = xmldoc.getElementsByTagName('ow').item(0).getElementsByTagName('name').item(0).firstChild.data;
	                	var base = xmldoc.getElementsByTagName('ow').item(0).getElementsByTagName('base').item(0).firstChild.data;
	                	var ubl  = xmldoc.getElementsByTagName('ow').item(0).getElementsByTagName('ubl').item(0).firstChild.data;
	                	var clan = xmldoc.getElementsByTagName('ow').item(0).getElementsByTagName('clan').item(0).firstChild.data;
	                	var dist = xmldoc.getElementsByTagName('ow').item(0).getElementsByTagName('dist').item(0).firstChild.data;
	                	var tf_e = xmldoc.getElementsByTagName('ow').item(0).getElementsByTagName('tf_e').item(0).firstChild.data;
	                	var tf_t = xmldoc.getElementsByTagName('ow').item(0).getElementsByTagName('tf_t').item(0).firstChild.data;
					    
                	} 
                	
                	if ((status_node.firstChild.data == 2)) {
                	
                		var dist = xmldoc.getElementsByTagName('ow').item(0).getElementsByTagName('dist').item(0).firstChild.data;
                		var ubl  = xmldoc.getElementsByTagName('ow').item(0).getElementsByTagName('ubl').item(0).firstChild.data;
                	
                	}
                	
                	if ((status_node.firstChild.data == 99)) {
                	
                		alert('session ungueltig');
                	
                	}                	
					
                	document.getElementById('name').innerHTML = name;
                	document.getElementById('base').innerHTML = base;
                	document.getElementById('clan').innerHTML = clan;
                	document.getElementById('ubl').innerHTML  = ubl;
                	document.getElementById('dist').innerHTML = dist;
                	document.getElementById('tf_e').innerHTML = tf_e;
                	document.getElementById('tf_t').innerHTML = tf_t;
                	
	            } else {
	            
    	            alert('There was a problem with the request.');
    	            
        	    }
        	}
       	
        }

        document.getElementById('name').innerHTML = 'loading ...';
        document.getElementById('base').innerHTML = 'loading ...';
        document.getElementById('clan').innerHTML = 'loading ...';
        document.getElementById('ubl').innerHTML  = 'loading ...';
        document.getElementById('dist').innerHTML = 'loading ...';
        document.getElementById('tf_e').innerHTML = 'loading ...';
        document.getElementById('tf_t').innerHTML = 'loading ...';
        document.getElementById('dist').innerHTML = 'loading ...';    
        
        http_request.open('GET', url, true);
        http_request.send(null);

    }

    
    // okay, here starts the clan show code
    
    function clan (clan, sector) {
    	clanmakeRequest('xml.php?'+session+'&clan='+clan+'&sector='+sector, sector);
	}
    
    function clanmakeRequest(url, sector) {

        http_request = false;

        if (window.XMLHttpRequest) { // Mozilla, Safari,...
            http_request = new XMLHttpRequest();
            if (http_request.overrideMimeType) {
                http_request.overrideMimeType('text/xml');
            }
        } else if (window.ActiveXObject) { // IE
            try {
                http_request = new ActiveXObject('Msxml2.XMLHTTP');
            } catch (e) {
                try {
                    http_request = new ActiveXObject('Microsoft.XMLHTTP');
                } catch (e) {}
            }
        }

        if (!http_request) {
            alert('XMLHTTP Instanz konnte nicht gestartet werden. Du scheinst einen alten Browser zu verwenden, oder du hast ActiveX ausgeschaltet.');
            return false;
        }
        http_request.onreadystatechange = function () {
        	
	        if (http_request.readyState == 4) {
    	        if (http_request.status == 200) {
        	    	
            	    var xmldoc = http_request.responseXML;
                	var status_node = xmldoc.getElementsByTagName('ow').item(0).getElementsByTagName('status').item(0);

                	var i=sector*500+1;
                	var e=sector*500+500-1;
                	while (i <= e) {
                		if (document.getElementById(i).className != 'kg2') {
                			document.getElementById(i).className = 'kg';
                		}
                		i++;
                	}
                	
                	var i=0;
                	while (xmldoc.getElementsByTagName('ow').item(0).getElementsByTagName('ubl').item(i)) {
                		
                		var ubl = xmldoc.getElementsByTagName('ow').item(0).getElementsByTagName('ubl').item(i).firstChild.data;
                		document.getElementById(ubl).className = 'ke';
                		i++;
                		
                	}
                	
	            } else {
	            
    	            alert('There was a problem with the request.');
    	            
        	    }
        	}
       	
        }

        http_request.open('GET', url, true);
        http_request.send(null);

    }