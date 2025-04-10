let listExperiences = [
    {
        "date": "2023-11-06",
        "startTime": "19:14",
        "endTime": "20:06",
        "kilometers": 45,
        "idExperience": 1,
        "idWeather":2,
        "idRoadType":4,
        "idTransmissionType":1,
        "idTrafficType":1
    },
    {
        "date": "2023-11-07",
        "startTime": "08:56",
        "endTime": "10:01",
        "kilometers": 79,
        "idExperience": 2,
        "idWeather":1,
        "idRoadType":4,
        "idTransmissionType":1,
        "idTrafficType":2
    },
    {
        "date": "2023-11-07",
        "startTime": "18:23",
        "endTime": "18:58",
        "kilometers":21,
        "idExperience": 3,
        "idWeather":4,
        "idRoadType":1,
        "idTransmissionType":2,
        "idTrafficType":3
    }
    ]

    let listWeather=[
    {
        "weather":"rainy",
        "idWeather":1
    },
    {
        "weather":"sunny",
        "idWeather":2
    },
    {
        "weather":"snowy",
        "idWeather":3
    },
    {
        "weather":"cloudy",
        "idWeather":4
    },
    {
        "weather":"mistyFoggy",
        "idWeather":5
    },
    {
        "weather":"sandstorm",
        "idWeather":6
    },
    {
        "weather":"stormy",
        "idWeather":7
    },
    {
        "weather":"tempestuous",
        "idWeather":8
    },
    {
        "weather":"partlyCloudy",
        "idWeather":9
    },
    {
        "weather":"clear",
        "idWeather":10
    }
]

    let listRoadTypes=[
    {
        "roadType": "concrete",
        "idRoadType": 1
    },
    {
        "roadType": "rocks",
        "idRoadType": 2
    },
    {
        "roadType": "ice",
        "idRoadType": 3
    },
    {
        "roadType": "sand",
        "idRoadType": 4
    },
    {
        "roadType": "mudOrDirt",
        "idRoadType": 5
    }
    ]

    let listTransmissionTypes=[
    {
        "transmissionType":"automatic",
        "idConditions": 1
    },
    {
        "transmissionType":"manual",
        "idConditions": 2
    }
    ]

    let listTrafficTypes= [
    {
        "trafficType":"none",
        "idTrafficType": 1
    },
    {
        "trafficType":"light",
        "idTrafficType": 2
    },
    {
        "trafficType":"heavy",
        "idTrafficType": 3
    }
    ]

    listWeather.forEach(function(item,index){
        let option='<option value="${item.idWeather}">${item.weather}</option?>'
        document.getElementById('idWeather'),insertAdjacentHTML('beforeend',option);
    });

    
    
    console.log(listExperiences);
    console.log(listWeather);
    console.log(listRoadTypes);
    console.log(listTrafficTypes);
    console.log(listTransmissionTypes);