# ernestbot
Discord Bot with multiple functions

ErnestBot is a Discord Bot with several functions. 
Each function is handled by a command. The commands are grouped into cathegories: fun, generalinfo, gamesinfo

# Category Info

### clima command

This command provides the wheather conditions in a city of Argentina.

Invocking :
  !ernestbot clima <city name>
  
  Where <city name> is part of the name of the city you want to get the wheather conditions.
  
Arguments:
  City name: The name or part of the name of a city. 
  Only part of the city name can be specified. The city name string entered will be searched in all the full city names provided in teh response from the SMN. If the argument string is found in more than one ful lcity names, the info of all the matching cities will be displayed up to a maximum of 20 cities.
  The search is done after converting vowels with accent to the corersponding without accent one.

  The folllowing exmaple provides the wheather conditions in Capital Federal city.
  
Example:

  clima capi
  
  
  Climate info
  
  Capital Federal
  
  Temp: 20.6 ST: 20.6
  
  Cond: Despejado Humi: 33
  
  Data from Servicio Metorologico Nacional
  
