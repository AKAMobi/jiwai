var map; //global

var Latlng={'北京':[39.89945,116.40969],'上海':[31.23385,121.4755],'天津':[39.14307,117.2001],'重庆':[29.56029,106.55355],'河北':[38.04502,114.52002],'河北石家庄':[38.04502,114.52002],'河北邯郸':[36.61044,114.49076],'河北邢台':[37.07036,114.49854],'河北保定':[38.87262,115.49815],'河北张家口':[40.81864,114.88464],'河北承德':[40.96843,117.93583],'河北唐山':[39.63617,118.20599],'河北秦皇岛':[39.94656,119.5808],'河北沧州':[38.30781,116.84271],'河北廊坊':[39.51937,116.7008],'河北衡水':[37.7313,115.6864],'山西':[37.87319,112.59966],'山西太原':[37.87319,112.59966],'山西大同':[40.09267,113.28761],'山西阳泉':[37.86083,113.57747],'山西长治':[36.20428,113.11301],'山西晋城':[35.50014,112.83657],'山西朔州':[39.32968,112.42729],'山西晋中':[37.68649,112.74621],'山西忻州':[38.40344,112.73961],'山西吕梁':[37.52305,111.1359],'山西临汾':[36.08199,111.52049],'山西运城':[35.02422,110.99076],'内蒙古':[40.81635,111.68104],'内蒙古呼和浩特':[40.81635,111.68104],'内蒙古包头':[40.65486,109.83404],'内蒙古乌海':[39.68081,106.81665],'内蒙古赤峰':[42.26299,118.95102],'内蒙古乌兰察布':[41.03318,113.10717],'内蒙古锡林郭勒盟':[43.93713,116.08243],'内蒙古呼伦贝尔市':[49.20731,119.76371],'内蒙古鄂尔多斯':[39.82015,109.99629],'内蒙古巴彦淖尔盟':[40.75587,107.41316],'内蒙古兴安盟':[46.0716,122.06656],'内蒙古阿拉善盟':[38.84842,105.64492],'内蒙古通辽':[43.60979,122.26579],'辽宁':[41.81073,123.38035],'辽宁沈阳':[41.81073,123.38035],'辽宁大连':[38.92425,121.62329],'辽宁鞍山':[41.10736,122.99014],'辽宁抚顺':[41.87913,123.90556],'辽宁本溪':[41.29104,123.76104],'辽宁丹东':[40.12404,124.37858],'辽宁锦州':[41.12001,121.12818],'辽宁营口':[40.67802,122.23395],'辽宁阜新':[42.01213,121.6496],'辽宁辽阳':[41.26683,123.16629],'辽宁铁岭':[42.29571,123.84595],'辽宁朝阳':[41.57195,120.44236],'辽宁盘锦':[41.19227,122.04954],'辽宁葫芦岛':[40.75609,120.85199],'吉林':[43.8929,125.32512],'吉林长春':[43.8929,125.32512],'吉林吉林':[43.85569,126.5646],'吉林四平':[43.16796,124.35953],'吉林辽源':[42.90147,125.14163],'吉林通化':[41.72584,125.93401],'吉林白城':[45.62103,122.84259],'吉林延边':[43.18806,129.42181],'吉林白山':[41.93672,126.42042],'吉林松原':[45.12118,124.81485],'江苏':[32.05892,118.79349],'江苏南京':[32.05892,118.79349],'江苏徐州':[34.2644,117.18011],'江苏连云港':[34.601,119.17806],'江苏淮安':[33.59535,119.01645],'江苏盐城':[33.38395,120.13363],'江苏扬州':[32.39429,119.44767],'江苏南通':[32.02597,120.85978],'江苏镇江':[32.20389,119.45006],'江苏常州':[31.79847,119.97573],'江苏无锡':[31.58131,120.31118],'江苏苏州':[31.31316,120.63331],'江苏泰州':[32.49174,119.91498],'江苏宿迁':[33.96095,118.29463],'江苏昆山':[32.05892,118.79349],'浙江':[30.25748,120.1673],'浙江杭州':[30.25748,120.1673],'浙江宁波':[29.8801,121.56081],'浙江温州':[28.02152,120.65878],'浙江嘉兴':[30.75119,120.74956],'浙江绍兴':[30.00989,120.58919],'浙江金华':[29.11044,119.67453],'浙江衢州':[28.93888,118.86995],'浙江舟山':[30.01514,122.10103],'浙江台州':[28.68495,121.43664],'浙江丽水':[28.4558,119.91244],'浙江湖州':[30.86689,120.09324],'安徽':[31.86646,117.29172],'安徽合肥':[31.86646,117.29172],'安徽淮南':[32.66046,117.04224],'安徽芜湖':[31.34967,118.3738],'安徽安庆':[30.52774,117.05042],'安徽黄山':[29.72064,118.32944],'安徽宿州':[33.6422,116.97726],'安徽巢湖':[31.60151,117.86134],'安徽池州':[30.65941,117.48284],'安徽六安':[31.75806,116.50035],'安徽阜阳':[32.91043,115.81086],'安徽马鞍山':[31.68128,118.4995],'安徽铜陵':[30.92991,117.80755],'安徽滁州':[32.30298,118.31417],'安徽宣城':[30.94691,118.75149],'安徽亳州':[33.87526,115.78067],'安徽淮北':[33.97454,116.78984],'安徽蚌埠':[32.94222,117.38358],'福建':[26.07912,119.30491],'福建福州':[26.07912,119.30491],'福建厦门':[24.46792,118.09924],'福建三明':[26.26706,117.63373],'福建莆田':[25.43473,119.00669],'福建泉州':[24.91486,118.59437],'福建漳州':[24.51441,117.65339],'福建南平':[26.64459,118.17304],'福建宁德':[26.66696,119.52011],'福建龙岩':[25.09539,117.02706],'江西':[28.66246,115.91655],'江西南昌':[28.66246,115.91655],'江西景德镇':[29.29635,117.20185],'江西萍乡':[27.62671,113.84902],'江西九江':[29.71515,115.98961],'江西鹰潭':[28.24294,117.02708],'江西上饶':[28.44695,117.96646],'江西宜春':[27.7996,114.3903],'江西抚州':[27.99251,116.35463],'江西吉安':[27.11408,114.98511],'江西赣州':[25.85251,114.93688],'江西新余':[27.82167,114.91368],'山东':[36.65319,116.97009],'山东济南':[36.65319,116.97009],'山东青岛':[36.10582,120.35483],'山东淄博':[36.80982,118.05825],'山东枣庄':[34.85406,117.56665],'山东东营':[37.43247,118.66868],'山东潍坊':[36.71303,119.11343],'山东烟台':[37.54736,121.38051],'山东威海':[37.50621,122.12184],'山东济宁':[35.41508,116.58144],'山东泰安':[36.18812,117.11217],'山东日照':[35.41602,119.5218],'山东莱芜':[36.21379,117.6694],'山东德州':[37.45018,116.29707],'山东滨州':[37.37758,118.0119],'山东临沂':[35.06265,118.33587],'山东菏泽':[35.24871,115.44983],'山东聊城':[36.45686,115.97994],'河南':[34.76714,113.67769],'河南郑州':[34.76714,113.67769],'河南开封':[34.79855,114.30335],'河南洛阳':[34.67194,112.44435],'河南平顶山':[33.73186,113.29903],'河南焦作':[35.24328,113.23372],'河南鹤壁':[35.89807,114.17749],'河南新乡':[35.3056,113.86393],'河南安阳':[36.09605,114.35347],'河南濮阳':[35.75972,115.02106],'河南许昌':[34.02552,113.82251],'河南漯河':[33.58199,114.01117],'河南三门峡':[34.77522,111.1923],'河南商丘':[34.44623,115.66306],'河南周口':[33.62657,114.63617],'河南驻马店':[32.9778,114.035],'河南信阳':[32.12872,114.06212],'河南济源':[34.76714,113.67769],'河南南阳':[32.99503,112.52067],'湖北':[30.58965,114.28742],'湖北武汉':[30.58965,114.28742],'湖北黄石':[30.2183,115.0907],'湖北襄樊':[32.02354,112.14802],'湖北十堰':[32.63073,110.77559],'湖北宜昌':[30.70277,111.29069],'湖北荆州':[30.32097,112.2809],'湖北鄂州':[30.40375,114.87261],'湖北孝感':[30.92749,113.91136],'湖北黄冈':[30.45047,114.86834],'湖北咸宁':[29.8865,114.26976],'湖北荆门':[31.04024,112.19408],'湖北随州':[31.72151,113.36015],'湖北天门':[30.65305,113.16019],'湖北仙桃':[30.37421,113.45985],'湖北潜江':[30.42576,112.89449],'湖北神农架':[31.75975,110.64906],'湖北恩施':[30.27527,109.48157],'湖南':[28.19602,112.99369],'湖南长沙':[28.19602,112.99369],'湖南株洲':[27.83166,113.12818],'湖南湘潭':[27.86387,112.9006],'湖南衡阳':[26.89977,112.60608],'湖南邵阳':[27.24231,111.47724],'湖南岳阳':[29.37775,113.11599],'湖南常德':[29.03453,111.69352],'湖南张家界':[29.13202,110.50037],'湖南娄底':[27.734,112.00175],'湖南郴州':[25.79673,113.03127],'湖南永州':[26.22927,111.61797],'湖南怀化':[27.55401,109.96634],'湖南益阳':[28.58542,112.34484],'湖南湘西':[28.31546,109.73358],'海南':[20.04501,110.35734],'海南海口':[20.04501,110.35734],'海南三亚':[18.25401,109.50617],'海南五指山':[18.83482,109.49483],'海南琼海':[19.2401,110.46144],'海南儋州':[19.5177,109.56457],'海南文昌':[19.61206,110.75499],'海南万宁':[18.8028,110.39715],'海南东方':[19.10314,108.65205],'海南澄迈':[19.73605,110.00616],'海南定安':[19.70332,110.32118],'海南屯昌':[19.36945,110.10212],'海南临高':[19.90886,109.6713],'海南白沙':[19.22758,109.4482],'海南昌江':[19.2629,109.05091],'海南乐东':[18.74977,109.16834],'海南陵水':[18.50818,110.03399],'海南保亭':[18.64144,109.69669],'海南琼中':[19.04164,109.8356],'广东':[23.13399,113.26006],'广东广州':[23.13399,113.26006],'广东深圳':[22.55197,114.11819],'广东珠海':[22.29112,113.57373],'广东汕头':[23.35911,116.68803],'广东韶关':[24.80609,113.59365],'广东河源':[23.74102,114.69069],'广东梅州':[24.30948,116.11278],'广东惠州':[23.11749,114.42965],'广东汕尾':[22.77951,115.35582],'广东东莞':[23.04873,113.75541],'广东中山':[22.52654,113.38081],'广东江门':[22.60173,113.10313],'广东佛山':[23.03751,113.11934],'广东阳江':[21.8606,111.97772],'广东湛江':[21.1961,110.39997],'广东茂名':[21.65274,110.91773],'广东肇庆':[23.05167,112.45882],'广东清远':[23.68204,113.04748],'广东潮州':[23.65934,116.61828],'广东揭阳':[23.53794,116.35886],'广东云浮':[22.93326,112.0375],'广西':[22.82527,108.33199],'广西南宁':[22.82527,108.33199],'广西柳州':[24.31427,109.40536],'广西桂林':[25.2771,110.2843],'广西梧州':[23.47352,111.30054],'广西北海':[21.48321,109.11769],'广西玉林':[22.62932,110.14988],'广西百色':[23.90521,106.63085],'广西防城港':[21.61852,108.34266],'广西钦州':[21.96307,108.6204],'广西贺州':[24.41308,111.54023],'广西河池':[24.69723,108.05621],'广西崇左':[22.41841,107.35153],'广西来宾':[23.73153,109.23091],'广西贵港':[23.09139,109.59715],'四川':[30.66431,104.07043],'四川成都':[30.66431,104.07043],'四川自贡':[29.34572,104.77335],'四川攀枝花':[26.58567,101.71719],'四川泸州':[28.89966,105.43945],'四川德阳':[31.12774,104.39523],'四川绵阳':[31.47708,104.76961],'四川广元':[32.44172,105.81581],'四川遂宁':[30.50645,105.57484],'四川内江':[29.58249,105.0553],'四川乐山':[29.55997,103.78573],'四川南充':[30.79134,106.07945],'四川宜宾':[28.77188,104.62066],'四川广安':[30.45832,106.62956],'四川达州':[31.2146,107.49528],'四川巴中':[31.85796,106.756],'四川雅安':[29.98857,103.00677],'四川眉山':[30.0449,103.82987],'四川资阳':[30.12419,104.65642],'四川阿坝':[31.90153,102.22008],'四川甘孜':[30.05372,101.96363],'四川凉山':[27.89231,102.25738],'贵州':[26.58719,106.7139],'贵州贵阳':[26.58719,106.7139],'贵州六盘水':[26.58937,104.8434],'贵州遵义':[27.69142,106.91718],'贵州铜仁':[27.7208,109.18765],'贵州毕节':[27.30777,105.28305],'贵州安顺':[26.24898,105.92962],'贵州黔西南':[25.0952,104.89379],'贵州黔东南':[26.58712,107.06885],'贵州黔南':[26.26107,107.51413],'云南':[25.03736,102.71037],'云南昆明':[25.03736,102.71037],'云南昭通':[27.33408,103.71062],'云南曲靖':[25.50314,103.79677],'云南玉溪':[24.35733,102.54419],'云南思茅':[22.78078,100.96942],'云南临沧':[23.88279,100.08709],'云南保山':[25.11575,99.16248],'云南丽江':[26.8791,100.23143],'云南文山':[23.37497,104.24233],'云南红河':[23.36901,103.38423],'云南西双版纳':[22.00887,100.79784],'云南楚雄':[25.03612,101.54456],'云南大理':[25.59422,100.22252],'云南德宏':[24.44558,98.54238],'云南怒江':[25.85594,98.85266],'云南迪庆':[27.82186,99.70384],'西藏':[29.67571,91.12836],'西藏拉萨':[29.67571,91.12836],'西藏那曲':[31.47795,92.05302],'西藏昌都':[31.1404,97.17947],'西藏山南':[29.21562,91.83258],'西藏日喀则':[29.27307,88.88501],'西藏阿里':[32.5402,80.15248],'西藏林芝':[29.65172,94.35819],'陕西':[34.30017,108.9976],'陕西西安':[34.30017,108.9976],'陕西铜川':[35.07052,109.07037],'陕西宝鸡':[34.37353,107.14178],'陕西咸阳':[34.33655,108.72093],'陕西延安':[36.59553,109.49127],'陕西渭南':[34.50081,109.50427],'陕西商洛':[33.91029,109.87527],'陕西安康':[32.68722,109.02437],'陕西榆林':[38.28888,109.74866],'陕西汉中':[33.07,107.01902],'甘肃':[36.07967,103.77926],'甘肃兰州':[36.07967,103.77926],'甘肃金昌':[38.52,102.18582],'甘肃白银':[36.54591,104.1705],'甘肃天水':[34.58078,105.71927],'甘肃嘉峪关':[39.79764,98.26752],'甘肃定西':[35.57928,104.62139],'甘肃平凉':[35.54409,106.6692],'甘肃庆阳':[35.72999,107.63826],'甘肃陇南':[33.77247,106.07975],'甘肃武威':[37.928,102.63599],'甘肃张掖':[38.93111,100.44999],'甘肃酒泉':[39.73952,98.5091],'甘肃甘南':[34.9845,102.90978],'甘肃临夏':[35.59653,103.20986],'青海':[36.62695,101.80335],'青海西宁':[36.62695,101.80335],'青海海东':[36.50046,102.10682],'青海海北':[36.93493,101.03672],'青海黄南':[35.51938,102.01414],'青海海南':[36.29086,100.67334],'青海果洛':[34.45331,100.26419],'青海玉树':[33.00301,97.00937],'青海海西':[37.37855,97.36883],'宁夏':[38.47426,106.28956],'宁夏银川':[38.47426,106.28956],'宁夏石嘴山':[39.01814,106.36317],'宁夏吴忠':[37.98149,106.19498],'宁夏固原':[36.00531,106.27737],'宁夏中卫':[37.34337,104.93868],'新疆':[43.80553,87.61988],'新疆乌鲁木齐':[43.80553,87.61988],'新疆克拉玛依':[45.59896,84.87199],'新疆石河子':[44.30433,86.07728],'新疆吐鲁番':[42.94195,89.1746],'新疆哈密':[42.82615,93.51358],'新疆和田':[37.11061,79.9352],'新疆阿克苏':[41.1639,80.27809],'新疆喀什':[39.46725,76.0124],'新疆克孜勒苏':[39.71826,76.16521],'新疆巴音郭楞':[41.7636,86.15416],'新疆昌吉':[44.009,87.30239],'新疆博尔塔拉':[44.89577,82.06015],'新疆伊犁':[43.91605,81.32064],'黑龙江':[45.73957,126.66254],'黑龙江哈尔滨':[45.73957,126.66254],'黑龙江齐齐哈尔':[47.34192,123.9592],'黑龙江鹤岗':[47.31386,130.28226],'黑龙江双鸭山':[46.64449,131.15046],'黑龙江鸡西':[45.30044,130.97651],'黑龙江大庆':[46.59626,125.00504],'黑龙江伊春':[47.72912,128.90458],'黑龙江牡丹江':[44.57863,129.61041],'黑龙江佳木斯':[46.80981,130.36151],'黑龙江七台河':[45.76782,130.99548],'黑龙江绥化':[46.63557,126.98372],'黑龙江黑河':[50.24523,127.49599],'黑龙江大兴安岭':[50.41868,124.11838],'香港':[22.31881,114.18144],'澳门':[22.19907,113.55438],'台湾':[23.60374,121.03795],'海外':[0,150],'阿尔巴尼亚':[19.5,41.2],'阿尔及利亚':[36.42, 3.08],'阿富汗':[34.31, 69.12],'阿根廷':[-34.36, -58.27],'阿联酋':[25.18, 55.18],'阿曼':[23.37, 58.35],'阿塞拜疆':[40.23, 49.51],'埃及':[30.03, 31.15],'埃塞俄比亚':[9.03, 38.42],'爱尔兰':[53.2, -6.15],'爱沙尼亚':[59.25, 24.55],'安道尔':[42.3, 1.31],'安哥拉':[13.3,-9],'安圭拉':[18.13, -63.04],'安提瓜和巴布达':[17.06, -61.51],'奥地利':[47.48, 13.03],'澳大利亚':[-37.49, 144.58],'巴布亚新几内亚':[-9.3, 147.1],'巴哈马':[25.05, -77.21],'巴基斯坦':[33.42, 73.1],'巴拉圭':[-25.16, -57.4],'巴勒斯坦':[31.43, 35.12],'巴林':[26.13, 50.35],'巴拿马':[8.58, -79.32],'巴西':[-15.47, -47.55],'白俄罗斯':[53.54, 27.34],'百慕大':[32.17, -64.47],'保加利亚':[42.41, 23.19],'北马里恶毒内群岛':[15.12, 145.43],'贝宁':[6.29, 2.37],'比利时':[50.5, 4.2],'冰岛':[64.09, -21.51],'波多黎各':[18.28, -66.07],'波兰':[21,52.15],'波斯尼亚和黑塞哥维那':[43.52, 18.26],'玻利维亚':[-19.02, -65.17],'伯利兹':[17.15, -88.47],'博茨瓦纳':[-24.45, 25.55],'不丹':[27.28, 89.39],'布基纳法索':[12.22, -1.31],'布隆迪':[-3.23, 29.22],'朝鲜':[39.01, 125.45],'赤道几内亚':[3.45, 8.46],'丹麦':[55.4, 12.35],'德国':[49.25, 8.43],'东帝汶':[-8.33, 125.35],'多哥':[6.08, 1.21],'多米尼加共和国':[18.28, -69.54],'多米尼克国':[15.18, -61.24],'俄罗斯':[55.45, 37.35],'厄瓜多尔':[0.13, -78.3],'厄立特里亚':[15.2, 38.53],'法国':[48.52, 2.2],'法罗群岛':[62.01, -6.46],'法属波利尼西亚':[-17.32, -149.34],'法属圭亚那':[4.56, -52.2],'梵蒂冈':[41.54, 12.27],'菲律宾':[14.35, 120.59],'斐济':[-18.08, 178.25],'芬兰':[60.1, 24.58],'佛得角':[14.55, -23.31],'冈比亚':[13.28, -16.39],'刚果':[-4.16, 15.17],'哥伦比亚':[4.36, -74.05],'哥斯达黎加':[9.59, -84.04],'格林纳达':[12.03, -61.45],'格陵兰':[64.11, 51.44],'格鲁吉亚':[41.43, 44.49],'古巴':[23.08, -82.22],'瓜德罗普':[-61.43,16],'关岛':[13.28, 144.45],'圭亚那':[5.2, -52.8],'哈萨克斯坦':[43.15, 76.57],'海地':[18.32, -72.2],'韩国':[37.33, 126.58],'荷兰':[52.22, 4.54],'荷属安的列斯':[12.12, -68.56],'洪都拉斯':[14.06, -87.13],'基里巴斯':[173,1.25],'吉布提':[11.36, 43.09],'吉尔吉斯斯坦':[42.54, 74.36],'几内亚':[9.31, -13.43],'几内亚比绍':[11.51, -15.35],'加拿大':[45.25, -75.42],'加纳':[5.33, -0.15],'加蓬':[0.23, 9.27],'柬博寨':[11.33, 104.55],'捷克':[50.05, 14.26],'津巴布韦':[-17.5, 31.03],'喀麦隆':[3.52, 11.31],'卡塔尔':[25.17, 51.32],'开曼群岛':[19.20, -81.13],'科摩罗':[-11.41, 43.16],'科特迪瓦':[6.49, -5.17],'科威特':[29.3, 47.59],'克罗地亚':[45.48, 15.58],'肯尼亚':[-1.17, 36.49],'库克群岛':[-21.12, -159.46],'拉脱维亚':[56.57, 24.06],'莱索托':[-29.15, 27.29],'老挝':[17.59, 102.38],'黎巴嫩':[33.53, 35.3],'立陶宛':[54.41, 25.19],'利比里亚':[6.18, -10.47],'利比亚':[32.54, 13.11],'列支敦士登':[47.08, 9.32],'留尼汪':[-20.55, 55.27],'卢森堡':[49.36, 6.09],'卢旺达':[-1.57, 30.04],'罗马尼亚':[44.26, 26.06],'马达加斯加':[-18.58, 47.3],'马尔代夫':[4.4, 73.3],'马耳他':[35.54, 14.31],'马拉维':[-13.59, 33.44],'马来西亚':[3.09, 101.43],'马里':[-8,12.39],'马其顿':[21.28,42],'马绍尔群岛':[7.09, 171.12],'马提尼克':[14.36, -61.05],'毛里求斯':[-20.1, 57.3],'毛里塔尼亚':[18.06, -15.57],'美国':[38.54, -77.02],'美属萨摩亚':[-14.16, 170.42],'美属维尔京群岛':[18.21, -64.56],'蒙古':[47.55, 106.53],'蒙特塞拉特':[16.42, -62.13],'孟加拉国':[23.42, 90.22],'秘鲁':[-12.03, -77.03],'密克罗尼西亚联邦':[6.58, 158.13],'缅甸':[16.47, 96.1],'摩尔多瓦':[28.5,47],'摩洛哥':[34.02, -6.51],'摩纳哥':[43.43, 7.25],'莫桑比克':[-26.1, 32.42],'墨西哥':[19.24, -99.09],'纳米比亚':[-22.34, 17.06],'南非':[-25.45, 28.1],'南斯拉夫':[44.5, 20.3],'瑙鲁':[-0.32, -166.55],'尼泊尔':[27.43, 85.19],'尼加拉瓜':[12.09, -86.17],'尼日尔':[13.31, 2.07],'尼日利亚':[9.1, 7.11],'纽埃':[-19.03, 169.55],'挪威':[59.56, 10.45],'帕劳':[7.2, 134.29],'皮特凯恩':[-25.04, -130.05],'葡萄牙':[38.43, -9.08],'日本':[35.42, 139.46],'瑞典':[59.2, 18.03],'瑞士':[46.12, 6.09],'萨尔瓦多':[13.42, -89.12],'塞拉利昂':[8.3, -13.15],'塞内加尔':[14.4, -17.26],'塞浦路斯':[35.1, 33.32],'塞舌尔':[-4.4, 55.2],'沙特阿拉伯':[21.27, 39.49],'圣多美和普林西比':[0.19, 6.43],'圣赫勒拿':[-15.56, -5.44],'圣基茨和尼维斯':[17.18, -62.43],'圣卢西亚':[14.01, -60.59],'圣马力诺':[43.56, 12.26],'圣皮埃尔和密克隆':[46.47, -56.11],'圣文森特和格林纳西斯':[13.09, -61.14],'斯里兰卡':[6.56, 79.51],'斯洛伐克':[48.1, 17.1],'斯洛文尼亚':[46.03, 14.31],'斯威士兰':[-26.18, 31.06],'苏丹':[15.36, 32.32],'苏里南':[5.5, -55.1],'所罗门群岛':[-9.27, 159.57],'索马里':[2.01, 45.2],'塔吉克斯坦':[38.35, 68.48],'泰国':[13.45, 100.31],'坦桑尼亚':[6.48, 39.17],'汤加':[-21.09, -175.14],'特克斯群岛和凯科斯群':[21.28, -71.08],'特立尼达和多巴哥':[10.39, -61.31],'突尼斯':[36.48, 10.11],'图瓦卢':[-8.31, 179.13],'土耳其':[41.01, 28.58],'土库曼斯坦':[38.23, 58.23],'托克劳':[-9.22, -171.14],'瓦利斯和富图纳':[-13.12, 176.12],'瓦努阿图':[-17.4, 168.2],'危地马拉':[14.38, -90.31],'委内瑞拉':[10.3, -66.56],'文莱':[4.56, 114.55],'乌干达':[0.19, 32.25],'乌克兰':[50.26, 30.31],'乌拉圭':[-34.53, -56.11],'乌兹别克斯坦':[41.2, 69.18],'西班牙':[40.24, -3.41],'西撒哈拉':[27.09, -13.12],'西萨摩亚':[-13.5, 171.44],'希腊':[37.05, 22.25],'锡金':[27.2, 88.37],'新加坡':[1.17, 103.51],'新喀里多尼亚':[-22.16, 166.27],'新西兰':[-41.18, 174.46],'匈牙利':[47.3, 19.05],'叙利亚':[33.3, 36.18],'牙买加':[17.58, -76.48],'亚美尼亚':[40.11, 44.3],'也门':[12.45, 24.12],'伊拉克':[33.21, 44.25],'伊朗':[35.4, 51.26],'意大利':[40.51, 14.17],'印度':[28.36, 77.12],'印度尼西亚':[-6.1, 106.48],'英国':[51.3, 0.1],'英属维尔京群岛':[-64.37, -64.37],'约旦':[31.57, 35.56],'越南':[10.45, 106.4],'赞比亚':[-15.25, 28.17],'扎伊尔':[-4.18, 15.18],'乍得':[12.07, 15.03],'智利':[-33.27, -70.4],'中非':[4.22, 18.35]};
/*var Latlng = {
  北京:[39.92,116.46],
  上海:[31.22,121.48],
  天津:[39.13,117.2],
  重庆:[29.59,106.54],
  浙江:[30.26,120.19],
  香港:[22.2,114.1],
  澳门:[22.13,113.33],
  台湾:[25.05,121.5],
  河北:[38.03,114.48],
  内蒙古:[40.82,111.65],
  辽宁:[41.8,123.38],
  山西:[37.87,112.53],
  吉林:[43.88,125.35],
  黑龙江:[45.75,126.63],
  江苏:[32.04,118.78],
  安徽:[31.86,117.27],
  福建:[26.08,119.3],
  江西:[28.68,115.89],
  山东:[36.65,117],
  河南:[34.76,113.65],
  湖北:[30.52,114.31],
  湖南:[28.21,113],
  海南:[20.02,110.35],
  广西:[22.84,108.33],
  四川:[30.67,104.06],
  贵州:[26.57,106.71],
  云南:[25.04,102.73],
  西藏:[29.97,91.11],
  陕西:[34.27,108.95],
  甘肃:[36.03,103.73],
  青海:[36.56,101.74],
  宁夏:[38.47,106.27],
  新疆:[43.77,87.68],
  广东:[23.16,113.23]
};*/

function initMap() {
  if (GBrowserIsCompatible()) {
	map = new GMap2(document.getElementById('map'));
	map.setCenter(new GLatLng(34.68491,112.47605), 6);
	map.addControl(new GLargeMapControl());
	map.addControl(new GOverviewMapControl());
  }
}

window.onload = function(){
	initMap();
	JiWaiTimeline.init();
	setInterval(JiWaiTimeline.heartbeat, 10000);

}

var JiWaiTimeline = {
	 public_timeline : []
	,current_pos : 0
	,api_url : 'http://api.jiwai.de/statuses/public_timeline.json'
	,fetch: function(apiUrl,num) { 
		if ( !num )
			num = 10;
		var s = document.createElement('script');
		s.type = 'text/javascript';
		s.src= apiUrl + '?' + Math.random() + '&callback=JiWaiTimeline.update&count=' + num + '&tim='+(new Date()).getTime();
		document.getElementsByTagName('head')[0].appendChild(s);
		s = null;
	}
	,update: function(jsonSource){
		var max_id = JiWaiTimeline.getMaxId();

//console.log("max_id: " + max_id);

		var p;
		for (var i=jsonSource.length-1; i>=0; --i){
			if ( jsonSource[i].id > max_id )
			{
//console.log( jsonSource[i].id + " is  larger than " + max_id );
				JiWaiTimeline.add(jsonSource[i]);
			}
			else
			{
//console.log( jsonSource[i].id + " is  less than " + max_id );
			}
		}

	}
	,init : function() {
		JiWaiTimeline.fetch(JiWaiTimeline.api_url, 5);
	}
	,heartbeat : function() {
//console.log ( "in heartbeat " + JiWaiTimeline.current_pos );
		if ( 0==JiWaiTimeline.current_pos )
			JiWaiTimeline.fetch(JiWaiTimeline.api_url);

//console.log ( "after fetch " + JiWaiTimeline.current_pos );
		status_data = JiWaiTimeline.public_timeline[JiWaiTimeline.current_pos];
//console.log ( "hb - " + status_data.user.location + ":" + JiWaiTimeline.current_pos );
		lh = JiWaiTimeline.formateStatus(status_data);
//console.log(lh.Latlng[0]);
		JiWaiTimeline.vision(lh);

		if ( JiWaiTimeline.current_pos > 0 )
			JiWaiTimeline.current_pos--;
		else
			JiWaiTimeline.current_pos = JiWaiTimeline.public_timeline.length-1;

		if ( JiWaiTimeline.current_pos<0 )
			JiWaiTimeline.current_pos = 0;
	}
	,getMaxId : function(){
		var n = JiWaiTimeline.public_timeline.length;
		if ( 0==n )
			return 0;

		return JiWaiTimeline.public_timeline[0].id;
	}
	,add : function(status_data){

//console.log ( "in add user " + status_data.user.screen_name  + " location: " + status_data.user.location + " current pos " + JiWaiTimeline.current_pos + " pub len: " + JiWaiTimeline.public_timeline.length);

		var latlng = false;	
		try {
			var l = status_data.user.location.replace(' ', '');
			if (/(北京|上海|天津|重庆)/.exec(l)){
				l = RegExp.$1;
			}
			var latlng = Latlng[l];

			if( !latlng ){
				l = JiWaiTimeline.getRandomCity();
				if( l )
					latlng = Latlng[l];
			}
			status_data.user.location = l;
		} catch(e) {
		}

		if ( !latlng )
			return;

//console.log ( "add user " + status_data.user.screen_name  + " location: " + status_data.user.location + " current pos " + JiWaiTimeline.current_pos + " pub len: " + JiWaiTimeline.public_timeline.length);


/*
			user_location = JiWaiTimeline.getRandomCity();

console.log ( "set user location : " + user_location );
*/

		JiWaiTimeline.public_timeline.unshift(status_data);
//console.log(status_data);
		JiWaiTimeline.current_pos++;

		while ( JiWaiTimeline.public_timeline.length >= 5 ){
//console.log("pop");
			JiWaiTimeline.public_timeline.pop();
		}


		if ( JiWaiTimeline.public_timeline.length && JiWaiTimeline.current_pos >= JiWaiTimeline.public_timeline.length )
			JiWaiTimeline.current_pos = JiWaiTimeline.public_timeline.length-1;

	}
	,getRandomCity : function(){
		//return false;
		c = 0;
		n = Math.floor(Math.random()*300);
		for ( l in Latlng )
		{
			if ( c++>n )
			{
				return l;
			}
		}
		return false;
	}
	,formateStatus : function (status_data){
		var j = status_data
		var html = '<div class="entry">';
		html 	+= '<p class="s' + Math.ceil(Math.random() * 10) + '">'
				+ j.text 
				+ '</p><a href="http://jiwai.de/' + j.user.screen_name + '/" target="_blank">'
				+ '<img src="' + j.user.profile_image_url + '" />'  + j.user.name + '</a> 于 ' 
				+ j.user.location 
				+ ' <em>' 
				+ '<a href="http://jiwai.de/' + j.user.screen_name + '/statuses/' + j.id + '" target="_blank">' 
				+ JiWaiTimeline.relative_time(j.created_at) 
				+  '</a></em></div>';

		return {
			Latlng: Latlng[j.user.location],
			Html: html
		};
	}
	,vision : function(lh){
		var point = new GLatLng(lh.Latlng[0], lh.Latlng[1]);
		map.openInfoWindowHtml(point, lh.Html);
	}
	,relative_time : function(time_value) {   
    	var values = time_value.split(" ");
    	time_value = values[1] + " " + values[2] + ", " + values[5] + " " + values[3];
                                            
    	var parsed_date = Date.parse(time_value);
                                            
    	var relative_to = (arguments.length > 1) ? arguments[1] : new Date();
    	var delta = parseInt((relative_to.getTime() - parsed_date) / 1000);
    
    	if(delta < 60) {
        	return '就在刚才'
    	} else if(delta < (60*60)) {
        	return (parseInt(delta / 60)).toString() + ' 分钟前';
    	} else if(delta < (24*60*60)) {
        	return (parseInt(delta / 3600)).toString() + ' 小时前';
    	}

    	return (parseInt(delta / 86400)).toString() + ' 天前';
	}
}
