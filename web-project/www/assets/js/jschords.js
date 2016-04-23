/*
 * First pass at a Javascript guitar chord finder.
 *
 * 23 May 05  Erich Rickheit KSC     Essentially a C translation
 *
 * Copyright 2005 Erich Rickheit. Permission is granted to use this
 * code as is, as long as this copyright notice is retained.
 * Permission is granted to modify this code, as long as this
 * copyright notice is retained and modifications are clearly marked.
 * All other rights are reserved to the author.
 */ 

// kinds of intervals
var TPERFECT     = 1;
var TMAJOR       = 2;
var TMINOR       = 3;
var TAUGMENTED   = 4;
var TDIMINISHED  = 5;

var intervals = [
/* none        */ [ ],
               /*      1  2  3  4  5  6    7   8   9  10  11  12  13  14 */
/* PERFECT    */ [ -1, 0, 2, 4, 5, 7, 9,  11, 12, 14, 16, 17, 19, 21, 23, 24 ],
/* MAJOR      */ [ -1, 0, 2, 4, 5, 7, 9,  11, 12, 14, 16, 17, 19, 21, 23, 24 ],
/* MINOR      */ [ -1, 0, 1, 3, 4, 6, 8,  10, 12, 13, 14, 16, 19, 20, 22, 24 ],
/* AUGMENTED  */ [ -1, 1, 3, 5, 6, 8, 10, 12, 13, 15, 17, 18, 20, 22, 24, 25 ],
/* DIMINISHED */ [ -1,-1, 0, 2, 4, 6, 7,   9, 11, 12, 14, 16, 18, 19, 21, 23 ],
];
  
// images of strings
var imgLeftOpen      = 'data:image/gif;base64,R0lGODlhIwDIAIAAAAAAAP///yH5BAEAAAEALAAAAAAjAMgAAAL/jI+py+0PFZh0RlgzvUv7zQXfCGIa45mVWqJrlDkxN0tveCc53h473RMFeRbEjwjQDYFF3/KyOzKTxiesJ4VirSxbMyT8OsXTTraRrV2tatk5zIWTvdLUmmSf4qlg+ahP9wY4SFhoCLb3oZc458IoOPbYaCY5GXh4iVkV19eG6XkIaihaSEpoOogKqNoJieiKxKcpNxspq8n6yqlrmQqrV2uQGxtMGzwMfPyrtUus3Jxci8zcu7p8d/tZWW37uMj4nRi+Fz07jV18zpIder2e7v6gLh/vBk3NPlqPtu/IzZuvVD9K/5xJG5jp4D10zwqWw4VQycJ3DQOeirjJIT54lxPpdbSnEZs3fCNFgiN50qS4jRU5hqSo8KVHmSAt+vrID6c/m9Z0EuTZymdCcxhtuQQK8KjSlkxjIjVIVKhEmjmp7lzqFGtUqz+1QpSaUdI4PGNJlP2DciXDrE23Pn2oDazRtl+5Dq37liXbvW69xrU7Ne/avnT/CoZJmC9ev+3kCiv62LExxYYZ65O8rWDmp5vPKkq7pwAAOw==';
var imgMidOpen       = 'data:image/gif;base64,R0lGODlhIwDIAIAAAAAAAP///yH5BAEAAAEALAAAAAAjAMgAAAL/jI+py+0PFZgURFhzvVJ7yhnfOHHecmKbCjqZuaLx9cplKLZJjfPH3NP9hMHb0Ig7WpTJnRCYnEGLyxyyKSVGn1qq8xqCTmnacWRqdnUDvrO5rV4rqw2Ne5y6k+zhPZy819TxJ1hoCOiXqLjI2Oj4CBmZxwJ2yJYmSGioWciZiYkFuiXXKUpFZ2lVeegZSvr56rq6adoXOzpbenua+pUL+yuLmtqKO2xZzNs7t8zcfLlrG2z8DD2t3JxsLcnd7f0N3g0TPX4tfcxaW45OS47Irmu+Xq09/1z/Tq+ef7+vBw8MoDB97v4RlMcvmz9KAqn1K8jwYENsy/AZfIjwosJoiuE6evwI0pu9jRkjYpx4TqLKkytJohxZceEDiyZdtowJcabMOCV15uT5MiHOnkBv9qLpk2idnUt/NlXKAGlRllRtvguJNatWkDCPMo361QZUsUE1Di1b86xRYmEHjXWLNmncqVbrqq16165Xp2D5kl2LrK2vuU8J9307GHA6v3AVtzO3NbLkyZEKAAA7';
var imgRightOpen     = 'data:image/gif;base64,R0lGODlhIwDIAIAAAAAAAP///yH5BAEAAAEALAAAAAAjAMgAAAL/jI+py+0PE5h0RlgzvUv7zQXfCGIa45mVWqJrlDkxN3ftVSNvaOz6TQP6eKLbkDc8hpLApbEZtPykxN6TWmViiUclrOllubZfaI4FkIUl62LbDZ16U2USPWon4/LpqvztFyg4GMh3h2bYV5eoiGiIx0iocCZJSWg5iCmoWQiI5OkU1ynqx1kKCinJRpqFusfK5bqo+kd7YNoK+6kbqpcpi2ZrxZsqjBtL/Oq7CfxwvLs8Gn2aPKsceZ0IqZ392M0nDNeo+tw7XtmsVh1snN5QXmwLfx0+by3vPjadu4/cD33uUj4b65wNnHRw1T9z9RLWaldQ3cJ4tOyxwxfxncNbmxuHLWQECiQvkR9JbvOGcSI9iCrvVewormFGfQF/zSTY8uLLmwh5KqzJzOfDlEClFaWW06BQjks9HuX31F9UgDKTSpzKkCVWiuRgWlRqVWPTmN/ynARX1s5Zs2lJVN26kujbuVrpyq2L967enWFp2uUL12XXsV+v/h3cF2dgnYgXg3VsOC/gw+gIe71seaTJtiPWquX8wbPbCwUAADs=';

var imgLeftUnplayed  = 'data:image/gif;base64,R0lGODlhIwDIAIAAAAAAAP///yH5BAEAAAEALAAAAAAjAMgAAAL/jI+py+0Po5zUgZpuAxwbrilgV41hNlKmlUbr87KgFC81dB853iK72wv8aK3hxGRUIU8eVLDpnEFFy6lvSZq+krAgl5H7Rpm6Z1cKNqfRG/WYyKbGz9l3yT2HW7TWvv8PiCWYtzZYBWeIdJR4CMQoJuSmJQkFuUg4iVlJ2WSJSNbn6Vj3J8qj2cnpYUoHasUqQ+oH24a6qopBW+iaKRuKe2ebC6wkHOz7SnyJ3Mu7aVzM/CydCr38SH2FDb1N3M397WwHGGn9Sf6hfI6ua6M+Kn5rDo9enj08f3p/HI+/H92PX712cv5dG/hOX0CACPO1amhwHTmC4wIlfMjuYqyFkwczOtyI7VrITyPhlVR4EuNEjbUi0vPoUiFEjhItfmxJ8+XKm7tm+oSZUybQnztjqrRpdCNRpEGPlmLZc6jUok2VTmW69Ok3kY+4MvKaCKyhjlSzzoLqjmfapDjN/lJbsGrbq1rZRi1L9yzcinXl3sWa963dtX4Ju022V9vguIebNZ5WmHFgxN3EDrIsCDMWzUsKAAA7';
var imgMidUnplayed   = 'data:image/gif;base64,R0lGODlhIwDIAIAAAAAAAP///yH5BAEAAAEALAAAAAAjAMgAAAL/jI+py+0Po5y0gZquBRrznn2gJI5HSaHbR4qQ6sCPrNCzu9gvjuiR7GuxAsFJqZg6mjA9pMeZPDJzSs60OYSurCdexabFcmve3ZChdarL22X3/GMbwu9xyG2OXYn7vv//VSU4SFhoeIiYqLjIWGgk10cnZAcoGQdXCblnaUb5x3mD+al5BRpDOmXaBiiGV4rKpIoG+yTqJ0tlG0kbqLvJG+V5C/zo+2qcSjzpmowcq3wpvOtc12h9jZ2tvT1YLP1L3ft9PN5c/hwezIx+Xtsuvu4eD89aXT8H3Tmvfk+UH/qO3z1cZNJ523ewH8E7ARMO/HfK4DKFEFc9lBgN4cSLigG5efwIMiRIh/UWtqKIUR/KhhtLVpyVEqDGjCtnquRoU2bNnTh5uowZkSXNnkR/Cr1pNGdQpRaT+mRl0l5RqMBEWr2KNWuYqPiANqXqFeZRnVMzhc01dulTs2m/smUqFi5auQXbxl076mxdugz5niyb1+5cvMP09iU8TfBexOAUH16mNbLkyYkKAAA7';
var imgRightUnplayed = 'data:image/gif;base64,R0lGODlhIwDIAIAAAAAAAP///yH5BAEAAAEALAAAAAAjAMgAAAL/jI+py+0Po5zUgZpuAxwbrilgV41hNlKmlUbr87KgFC81dB853iK72wv8aK3hxGRUIU8eVLDpnEFFy6lvSZq+krAgl5H7Rpm6Z1cKNqfRG/WYyKbGz9l3yT2HW7TWvv/PgyVI1jaIdWR4CJcoJsSIBFiWZ9WIOKnlhnkJVblISJnJGdrUCbRJOupRGlj3t0r3qdnq9yoz21dbeAt6qpqKkbu2Kxsr2gv8e3esPGzcjLqsFG357Bv9COmJTY1dPLat/Rj5kSxdjTztOe6YbnrO7G39bh6PPs+9HmxTjj+uL3dPnT9+AiP9s2OQoLt68PIpZMWQnsN2ECcGXGgxYr+EkxRhcQO3ECREkbBI2uqWMeXAjidVcrxYcSVMjzI1FgR08ApLXTYx1nSJ8yHNlz1jEgXqSmjLn0yPNg26U1jRoVBnLnUaUlw4Rh+1ZuW6NRFSWkp5jsVVVupZXlbNPk0adV9cgFOvVq3rFuvdtcT4OsOr9i3ZuQj3CkZLWGfbwHrhLpb7mG5XsF/FhjU02XJlzBIKAAA7';

var imgLeft = [
  'data:image/gif;base64,R0lGODlhIwDIAIAAAAAAAP///yH5BAEAAAEALAAAAAAjAMgAAAL/jI+py+0Po5y02ouz3rz7D4biSJbmiabqyrbuC8fyTNcTgOf6vt/8z/MBhwAh8Wc89iRLUPPz7AClx4xSZ7lGH9pgpIvkgsONMXlhnjLSZwRbnXi3DXJvvI5V4PP3fVHv99e3t0ZYiOeAmFgHIceURmGW1WVVxQFHlSOydckH5enBlkT5KFk6NqqVtAmaicOqGcK5MatRWxnr1NqZ+9kbuksbbDuM+ypbjHGrnHyx7Nw8+esq6HusO817bV0NnC38TRxu3E0Nu+2Nbo48zmz6BXkKlnpFr7TKrq5dvn/OD64P4D9xAQkOJOcvYb6D7goiXKgQm8OGDKG1szgRY0VpXBk5bqzwzGNEbiPTfYwUDWRKlBdFQlxICt67RvFkopIXk2ZLlTtZduT50+dJfBKHyiu5rijSfi+VNiX51ORSgVMNVn3oNCtUrVKjJt0KtmvRnGJmlr1pcx5OLQUAADs=',
  'data:image/gif;base64,R0lGODlhIwDIAIAAAAAAAP///yH5BAEAAAEALAAAAAAjAMgAAAL/jI+py+0Po5y02ouz3rz7D4biSJbmiabqyrbuC8fyTNcTgOf6vt/8z/MBhwAh8Wc89iRLUPPz9EQ7U051c9VkM1tM9/K1hCtjSjkpOjN1aXZIHYFD5A+6w97AM/QLvsKfAIggeEBoYBiAqOjmxAilhLQGWTYZJFmJk1SJ9uYo5WkViXXEtUmGWaQ5eYmaetfaBbsla9lHC3p4W6W7y5tj6+s6GCxcSAzsi8y7l8y8nKdbFy0ty2pqrSR2rU1EJer92xneOP5Y/tmqCqmejb3u3h6HO3oOnil+T55vvo/eb18MYJt6oQjS+1cQ4cGACRkuHKhQyzyJBilGLFUR40UvZhM1OrT40SPEkBwzltwIpuNJkilNtkTJDeYplzExsSMFD6e8dDm7sRoJFB/LmkH1DZ0p04zKl0eV0kTalJPRovyi/hRK1Z/VnUmlVs0qEKvYqWO/ltUKtmHahduoobrpk6vNnkMKAAA7',
  'data:image/gif;base64,R0lGODlhIwDIAIAAAAAAAP///yH5BAEAAAEALAAAAAAjAMgAAAL/jI+py+0Po5y02ouz3rz7D4biSJbmiabqyrbuC8fyTNcTgOf6vt/8z/MBhwAh8Wc89iRLUPPz9EQ7U051c9VkM1tM9/K1hCtjSjkpOjN1aXZIHYFD5A+6w97AM/QLvsKfAIggeEBoYBiAqOjmxAilhLQGWTYZJFmJk1SJ9uYo5UkFaiWKRaplyoXqpQrGKuZKBmsmy9mY03bbmWubqdvLW+QbDIz7+7h7bPyJvKwcyvzsPAo9LV1KfW19ir2tnYrpHQgePjg+PGeuOVmbfN7sXk2bd/QN+Yo5O36pX2eu6o8KYBaBpAhWMSgKITWF0hjCS8SwT8Q/EykanHcRI8B+YALR+du3SR29WOtWEYkmjyO5eytJtsz3UqQSmSPjpANZ0iY/nTH3FXuYDWg3ofWImuzJ02grbkV/OhX2lBjUqVKrtot6lWpWq++wdtX6lStKr2PBogyZFK1HcDRP4oRUAAA7',
  'data:image/gif;base64,R0lGODlhIwDIAIAAAAAAAP///yH5BAEAAAEALAAAAAAjAMgAAAL/jI+py+0Po5y02ouz3rz7D4biSJbmiabqyrbuC8fyTNcTgOf6vt/8z/MBhwAh8Wc89iRLUPPz9EQ7U051c9VkM1tM9/K1hCtjSjkpOjN1aXZIHYFD5A+6w97AM/QLvsKfAIggeEBoYBiAqOjmxAilhLQGWTYZJFmJk1SJ9uYo5UkFaiWKRaplyoXqpQrGKuZKBmsmy9mY03bbmWubqdvLW+QbDIz7+7h7bPyJvKwcyvzsPAo9LV1KfW19ir2tnYrpHQgePjg+PGeuOVmbfN7sHg1fLZ9N323/Tf7KnY+/yv9PXyyArQjuEzjL4ECE7N4V81eQ4SWIBykutJhQYhxaahMfehT2kRjIkSKBbZp4cuM4dZBYKmkYL+Q7hXWO9LN5saVLnOjMQfSpCig/oaaIYjMqCik1pdKYynPah2lUpVOR7rF61egdojWF9kynElzGdTmJ3LRUj2ZYjDDnyZxJdm3Kr5h2mkU5qQAAOw==',
];

var imgMid = [
  'data:image/gif;base64,R0lGODlhIwDIAIAAAAAAAP///yH5BAEAAAEALAAAAAAjAMgAAAL/jI+py+0Po5y02ouz3rz7D4biSJbmiabqyrbuC8fyTNfAjef6zvf+DwwKh8Si8Zib6ETLUNPT6wQzRcuRggRIsjcIF/f4dhtiMKM8VqDNiXUa4daq3Yt4nT5f3/V7MRntUBbmN5i1hYRlZEVEBSTF45QUyfbxVCkZEae5ydkJeEgJhXkZ+ljKYSl6upFq+qb66ioH0oo6CjtLGmu7qlHLeivLFMy7C9zbiIzx60t8bNysfMGcDF2dizssvbhdQb3sHG3d5llufn6uFH6tPT69Dt6dKK9OD+rOje8N/26fyZ8Pm7BJ+uYVrHfwnsBiC581FPeQHcGI8RL+8+cF4D6MVoUsZuQYSKNBiv08diQZsB1KA+haunzJBeHKkSprTrRJS6RMnLpm7rwJNCfIP0PP6FTIM1vQnkkHCjUZsmgfn0iXKn1K9SJUoluNSs3TdapMmGTLmi0AADs=',
  'data:image/gif;base64,R0lGODlhIwDIAIAAAAAAAP///yH5BAEAAAEALAAAAAAjAMgAAAL/jI+py+0Po5y02ouz3rz7D4biSJbmiabqyrbuC8fyTNfAjef6zvf+DwwKh8Si8Zib6ETLUBP0/EQ9006Vc91kNdtMF/O9hC3jSplyViada2hb+qbGrXNsXXvn5r17cF/8RxZoNohWqIbDdCiRxliIBBkpOUlZGYl4o5jItunWSbcD6sM3JFhkeOQICUH50NpgCVvJYJmpUAuwgKtbyxt72wv8m7DrOyn7SnvskIyMpGqESmQqRDpqF+ppq53Luc2KGy4+Tt7suhjRmI4O/innLtrNrfn9Xh9PLw8Hj33fr28PID5vAv/lO0gQ4byEDBc63OcPDz+JEfVMtFjRWkGKRhsxdhRWLqTIkSExffRzUaNCiCcBpUSZEWZLajMJvXQZE2dNaTtNrgz4c+BDoA1ZBjVYlOhQoUaTMlXadCnSdSSrWr2KpAAAOw==',
  'data:image/gif;base64,R0lGODlhIwDIAIAAAAAAAP///yH5BAEAAAEALAAAAAAjAMgAAAL/jI+py+0Po5y02ouz3rz7D4biSJbmiabqyrbuC8fyTNfAjef6zvf+DwwKh8Si8Zib6ETLUBP0/EQ9006Vc91kNdtMF/O9hC3jSplyViada2hb+qbGrXNsXXvn5r17cF/8RxZoNohWqIbDdCiRxliIBBkpOUlZGYl4o5jItunWCfcpF0o3aleKd6qXyrfq1wr4KhhLOGtYiwmgmcm569kL+isaTDpsWox6rJrMuuzaDPssG007bVuNq5vLqx1h6f0NHi7+uNhdDtFofovOQ9zDHCQ9ZF3kCMku+UDpYNnQv/ANoDeB/xIEVHDQ4ECECxUWdDjJXyV+ESnm03dJnRF6TULkAYHXztg5i9fsldTIDd+4lSxbuqyWTmVKYTPdZbu5DaevmiJ1AuOJDKgyofB80jRqM6fSnUh7Lv3ZNGjUoVOLPj16leTLrVy7GikAADs=',
  'data:image/gif;base64,R0lGODlhIwDIAIAAAAAAAP///yH5BAEAAAEALAAAAAAjAMgAAAL/jI+py+0Po5y02ouz3rz7D4biSJbmiabqyrbuC8fyTNfAjef6zvf+DwwKh8Si8Zib6ETLUBP0/EQ9006Vc91kNdtMF/O9hC3jSplyViada2hb+qbGrXNsXXvn5r17cF/8RxZoNohWqIbDdCiRxliIBBkpOUlZGYl4o5jItunWCfcpF0o3aleKd6qXyrfq1wr4KhhLOGtYiwmgmcm569kL+isaTDpsWox6rJrMuuzaDPssG007bVuNq5vLqx1h6f0NHi7+uNhdDtFofuu4rn7N/u7ODTwvXE98b5yPvK/cz/zPWUBoA6UVpHbQWkJs27I5bAjR10J4E+U9lHiRXkZ7WLjGefwIMiRDjBE10mu3IAhAIAaFKCRC0Qi6SQ8oObDUAGdKbzt1JvjWs1JQmwqAFuV51OdPoQyUJr10k2ZNqDOPvFSJkKVAHvhQRvX6NV7VaiHLmj1bqQAAOw==',
];

var imgRight = [
  'data:image/gif;base64,R0lGODlhIwDIAIAAAAAAAP///yH5BAEAAAEALAAAAAAjAMgAAAL/jI+py+0Po5y02ouz3rz7D4biSJbmiabqyrbuC8fyTNfAjee6Pu2+3/sJb8Hhr2jciXghJsjZOXKSmWTOYoVCssoIF/j4ShtiMKM8VqDNiXX64GbD41oDvR64X9V6Ir+/0Of3R0em53AXVqi4JtGIlIXFVWUUJTe195T5gbfRGYgGOekYSlomGtm06fFJibO0avmqOqtZyxmLecua69mr0YoRfDEsuSs7iHusm8y77PsM/OvajAwAG019TVvNvG3bDR0unS08bV5OfK6ebjxO+IVqJU9lKkZfyf2t/I7ez/7P3T5nASsUM7hOILaCFA42TIiwXUSGkBYOtGYxoz6NX+AueuPIz6M4keQo2iOpDSRBlP5YAnSpcOPJUV5K1Tw1M9XNezI7qsTYM+TPj0FXDh15tCTMiUsfSnRqcmfTikWB+qxK9KpWoViRdlWaNOVXsVJpbrF5FmdZnWl5RigAADs=',
  'data:image/gif;base64,R0lGODlhIwDIAIAAAAAAAP///yH5BAEAAAEALAAAAAAjAMgAAAL/jI+py+0Po5y02ouz3rz7D4biSJbmiabqyrbuC8fyTNfAjee6Pu2+3/sJb8Hhr2jciXghJsj5gXqkHSrHusFqtBkuxnsBW8QVMsWMBCxza1yb2GTH3XP4U36n5+1RfF//xzflNwhYKFhFmGi4iHil2JB0JCEJRFlphpmpyfjYmQUJ+vllKSrUhTnGecbpuNAaigDrNRsbUKuFG6prxQvp2wnsKKymQPwqjAzMsMzs68D7EC1de9nKqqlaiTrUaBv2DT7K7boVfjCblmq9yp7tvr5XLP82H2h/iO9dz1+n71mO3D9TA82NI3VQXECECxUWFNiPnr+I9yjms7hvokaJURwrbvTYkdo1eNtISlJXMkK6jxdZZgz5EqTMljABPmR40yFGmzsJ9jTYUFvQMueIJhSaE+lPiC55NvX5FGhSo0OxVU2D8qTJJFm5bjXSFayEAgA7',
  'data:image/gif;base64,R0lGODlhIwDIAIAAAAAAAP///yH5BAEAAAEALAAAAAAjAMgAAAL/jI+py+0Po5y02ouz3rz7D4biSJbmiabqyrbuC8fyTNfAjee6Pu2+3/sJb8Hhr2jciXghJsj5gXqkHSrHusFqtBkuxnsBW8QVMsWMBCxza1yb2GTH3XP4U36n5+1RfF//xzflNwhYKFhFmGi4iHil2JB0JCEJRFlphpmpyfjYmQUJ+rkVSjraVYp6+pXKuhrWCvs6Fks7W1aLe3uWy7ub9qZWJ7xHHGh8iNyo7Oko6mwKrSrtSi1rbYutq+3LDTychhleOS5ZnnRuFLwOfvwbqV6Nvk2ePnTJ+Zqv5LDP5c9vAUAsAwMiKGgFISSFnRg6csgMokCHDBjCQ9iv4AOMVxsB4uPUrV7IeNcmPev1kdlJdsXsCXFpEoI/mJYizGzXEucxljt1JuP50+cyoEOFNlMZDek0pfKIHnW60mjUnFR7Vg16tWjWp1In5qNpsOPXlOLIioRQAAA7',
  'data:image/gif;base64,R0lGODlhIwDIAIAAAAAAAP///yH5BAEAAAEALAAAAAAjAMgAAAL/jI+py+0Po5y02ouz3rz7D4biSJbmiabqyrbuC8fyTNfAjee6Pu2+3/sJb8Hhr2jciXghJsj5gXqkHSrHusFqtBkuxnsBW8QVMsWMBCxza1yb2GTH3XP4U36n5+1RfF//xzflNwhYKFhFmGi4iHil2JB0JCEJRFlphpmpyfjYmQUJ+rkVSjraVYp6+pXKuhrWCvs6Fks7W1aLe3uWy7ub9qZWJ7xHHGh8iNyo7Oko6mwKrSrtSi1rbYutq+3LDTychhleOS5ZnnRuFLwO3l7M/u5+DD8vn0x/b7+Mv6/fzPwMYDSB0whWM3gNYTaF2xh2c/gtnsR6E/NF4LQLIzeNWg45phvC71/Igr8iqTt48qG5j0IueXzAsVXMUzNL1fx0E1LOTjsd9WT2c0FQBUMTFDWa0+RNB0uZ1oQw0yUnlegaplxoKWBJqL0iUvwKUyPLSRfFStU0NiuEAgA',
];

// maps note names to half-steps
var steps = { c: 0, d: 2, e: 4, f: 5, g: 7, a: 9, b: 11 };

// a global error location
var errpar;

function err_mesg(mesg)
{
  if (errpar == null) errpar = document.getElementById("errpar");
  errpar.appendChild(document.createTextNode(mesg));
  errpar.appendChild(document.createElement('BR'));
}

var stringbase;   // notes on each open string
var results;

function findvoice()
{
  // start by parsing the chord
  var chordname = document.getElementById("chordval");
  var chordval = parse_chord(chordname.value);
  if (!chordval)
    { err_mesg('Nemůžu zobrazit akord. <a href="/zpevnik/chords/help">Nápověda</a>'); return; }

  // turn intervals into note numbers (half-steps above C)
  var notevals = new Array();
  notevals.push(chordval.root);
  for (var i=2; i<16; i++)
  {
    if (chordval[i] <= 0) continue;
    if (chordval[i] == null) continue;
    var val = chordval.root + intervals[chordval[i]][i];

    // confine to first octave
    while (val >= 12) val -= 12;
    while (val <  0)  val += 12;

    notevals.push(val);
  }
  notevals.bassnote = chordval.bassnote;

  // identify string tunings
  stringbase = new Array();
  var fingering = new Array();
  for (i=1; i<=6; i++)
  {
    var sval = document.getElementById("s"+i).value;
    if (sval == 'x') continue;
    var nval = steps[sval.substr(0,1).toLowerCase()];
    if (sval.substr(1,1) == '#') nval++;
    if (sval.substr(1,1) == 'b') nval--;
    stringbase.push(nval);
    fingering.push(-1);
  }

  results = new Array();

  // start looking
  consider_string(fingering, notevals, 0);

  // results will go into a table in this div; 
  var resDiv = document.getElementById("resultsdiv");

  // clean out any previous results
  while (resDiv.hasChildNodes())
    resDiv.removeChild(resDiv.firstChild);

  // table to arrange results
  var table = document.createElement('div');
  resDiv.appendChild(table);

  // put results into order
  results.sort(
    function(a, b)
    { var res;

      res = a.min - b.min;            // closer to the nut show up earlier
      if (res != 0) return res;

      res = a.unplayed - b.unplayed;  // fewer unplayed strings earlier
      if (res != 0) return res;

      res = b.open - a.open;          // more open strings earlier
      if (res != 0) return res;

      return res;
    }
  );
  for (j=0; j<results.length; j++)
  {
    // time for a new row
    if (j%1 == 0)
    {
      resultrow = document.createElement('div');
      resultrow.className = 'chordBox col-xs-12 col-sm-6 col-md-4 col-xl-3';
      table.appendChild(resultrow);
    }

    // where to draw that chord
    var box = document.createElement('div');
    box.className = "chordInnerBox";
    resultrow.appendChild(box);

    // the fret number
    min = results[j].min;
    if (min > 1) {
      var fretNum = document.createElement('div');
      fretNum.className = "fretNum";
      box.appendChild(fretNum);
      fretNum.appendChild(document.createTextNode(min+'. pražec'));
    }

    // the left hand image
    if      (results[j][0] == -1) fret = imgLeftUnplayed;
    else if (results[j][0] ==  0) fret = imgLeftOpen;
    else fret = imgLeft[results[j][0] - min];
    img = document.createElement('IMG');
    img.src = fret;
    img.alt = results[j][0];
    img.align = 'TOP';
    box.appendChild(img);

    // the images between
    for (i=1; i<results[j].length-1; i++)
    {
      if      (results[j][i] == -1) fret = imgMidUnplayed;
      else if (results[j][i] ==  0) fret = imgMidOpen;
      else fret = imgMid[results[j][i] - min];
      img = document.createElement('IMG');
      img.src = fret;
      img.alt = results[j][i];
      img.align = 'TOP';
      box.appendChild(img);
    }

    // the right hand image
    if      (results[j][i] == -1) fret = imgRightUnplayed;
    else if (results[j][i] ==  0) fret = imgRightOpen;
    else fret = imgRight[results[j][i] - min];
    img = document.createElement('IMG');
    img.src = fret;
    img.alt = results[j][i];
    img.align = 'TOP';
    box.appendChild(img);
  }
}

function findargs()
{ var p;
  var val;

  if ((p = location.search.indexOf('ch=')) >= 0)
  {
    val = location.search.substr(p+3);
    if ((p = val.indexOf('&')) >= 0) val = val.substr(0, p);
    if (val.length > 0)
    {
      val = unescape(val);
      document.getElementById("chordval").value = val;
      findvoice();
    }
  }
}

// does the fingering play the chord
function check_match(fingering, notes)
{ var note;
  var i;

  // every note in the chord must be played
  for (note=0; note<notes.length; note++)
  {
    var played = false;
    for (i=0; i<fingering.length; i++)
    {
      if (fingering[i] < 0) continue;  // ignore unplayed strings
      pitch = stringbase[i] + fingering[i];
      while (pitch >= 12) pitch -= 12;
      while (pitch <0)    pitch += 12;
      if (pitch == notes[note]) { played = true; break; }
    }

    // note wasn't played on any string
    if (!played) return;
  }

  // if there's a bass note, it must be on the lowest string
  if (notes.bassnote != null)
  {
    for (i=0; i<fingering.length; i++)
    {
      if (fingering[i] < 0) continue;  // ignore unplayed strings
      pitch = stringbase[i] + fingering[i];
      while (pitch >= 12) pitch -= 12;
      while (pitch <0)    pitch += 12;
      if (pitch == notes.bassnote) break;  // OK, that's it
      return;  // wasn't on the lowest played string
    }
  }

  // guess it's a chord; emit it
  /*
  note = 'match:';
  for (i=0; i<fingering.length; i++)
    note = note + ' ' + fingering[i];
  err_mesg(note);
  */

  // create a result
  var res = new Array();
  var max = 0; var min = 1000;
  res.unplayed = res.open = 0;
  for (i=0; i<fingering.length; i++)
  {
    res[i] = fingering[i];
    if (fingering[i] > 0 && fingering[i] > max) max = fingering[i];
    if (fingering[i] > 0 && fingering[i] < min) min = fingering[i];
    if (fingering[i] < 0)  res.unplayed++;
    if (fingering[i] == 0) res.open++;
  }
  if (max < 5) min = 1;
  res.min = min;
  results.push(res);
}


// recursively tries to find the notes on the strings
function consider_string(fingering, notes, string)
{ var note;
  var pos;
  var count;
  var i;

  // hey, did we find one?
  check_match(fingering, notes);
  if (string >= fingering.length) return;

  // try to find each note on this string
  Note: for (note = 0; note < notes.length; note++)
  {
    // find it, normalize to first octave
    pos = notes[note] - stringbase[string];
    while (pos < 0)  pos += 12;
    while (pos > 11) pos -= 12;

    // err_mesg('placing '+ notes[note] + ' on string '+ string +', fret '+pos);

    // string is fretted
    if (pos > 0)
    {
      // how many strings are currently fretted?
      for (i=count=0; i < string; i++)
	if (fingering[i] > 0) count++;
      
      if (count >= 3) // four fingers
      { // would fail for an open chord -- what about a barre?

        // find lowest fretted string; that's the barre
	var barre = pos;
	for (i=0; i<string; i++)
	{
	  // a barre can't have any open strings
	  if (fingering[i] == 0) continue Note;
	  if (fingering[i] < 0) continue;     // ignore unplayed strings
	  if (fingering[i] < barre) barre = fingering[i]
	}

	// now, count strings fretted above the barre
	for (i=count=0; i < string; i++)
	  if (fingering[i] > barre) count++;
	if (pos > barre) count++;

	// four fingers, index finger is barring
	if (count > 3) continue Note;
      }

      // is the reach between strings too great?
      var min = pos; var max = pos;
      for (i=0; i<string; i++)
      {
	if (fingering[i] > 0 && fingering[i] > max) max = fingering[i];
	if (fingering[i] > 0 && fingering[i] < min) min = fingering[i];
      }

      // we can reach 4 frets
      if ((max - min) > 3) continue Note;
    }

    // OK, no reason to fail
    fingering[string] = pos;
    consider_string(fingering, notes, string+1);
    fingering[string] = -1;
  }

  // try this string unplayed?
  for (i=0; i<string; i++)
    if (fingering[i] >= 0) return; // can't if any below are played
  fingering[string] = -1;
  consider_string(fingering, notes, string+1);
}


// a simple recursive-descent parser for chord names
function parse_chord(name)
{
  // split the string into an array of characters
  var inbuf = new Array;
  for (var i=0; i<name.length; i++)
  {
    var ch = name.charAt(i);
    if (ch == ' ' || ch == '\t' || ch == '\r' || ch == '\n') continue;
    inbuf.push(ch.toLowerCase());
  }

  // a place to put our result
  var rchord = new Object();

  // first thing is the root note
  if ((rchord.root = parse_note(inbuf)) < 0) return null;

  // build the major triad
  rchord[3] = TMAJOR;
  rchord[5] = TPERFECT;

  // variations come afterward
  if (!parse_minor(inbuf, rchord)) return null;
  if (!parse_mods(inbuf, rchord)) return null;
  if (!parse_bass(inbuf, rchord)) return null;

  // some of the string was left unparsed
  if (inbuf.length > 0 && inbuf[0] != 0) return null;

  return rchord;
}

// looks for a note; returns its numeric value, or -1 on failure
function parse_note(inbuf, rchord)
{
  // easy check for non-note
  if (inbuf.length <= 0) return -1;
  if (inbuf[0] < 'a' || inbuf[0] > 'g') return -1;

  // consume it, convert to a number of half-steps
  var note = inbuf.shift();
  note = steps[note];

  // a group of sharps
  if (inbuf[0] == '#')
  {
    while (inbuf[0] == '#')
      { inbuf.shift(); note++; }
    if (note >= 12) note -= 12;
  }
  // or a group of flats (can't have both)
  else if (inbuf[0] == 'b' || inbuf[0] == '!')
  {
    while (inbuf[0] == 'b' || inbuf[0] == '!')
      { inbuf.shift(); note--; }
    if (note < 0) note += 12;
  }

  return note;
}

// check if a chord is identified as a minor. Returns false on error
function parse_minor(inbuf, rchord)
{
  // quick check
  if (inbuf[0] != 'm') return true;

  // but don't be fooled by a 'maj' notation
  if (inbuf[1] == 'a' && inbuf[2] == 'j') return true;

  // consume that notation
  inbuf.shift();

  // accept 'min' as well
  if (inbuf[0] == 'i' && inbuf[1] == 'n')
    { inbuf.shift(); inbuf.shift(); }

  // turn a major third into a minor third
  rchord[3] = TMINOR;

  return true;
}

// just a list of zero or more mods; returns false on error
function parse_mods(inbuf, rchord)
{ var res;

  while (res = parse_mod(inbuf, rchord))
    if (res < 0) return false;
  return true;
}

// parse a single modification to the chord;
// returns 1 if a mod is found, 0 if none found, -1 on an error
function parse_mod(inbuf, rchord)
{ var n;

  switch (inbuf[0])
  {
    case 'a':
      if (inbuf.length < 3) return 0;
      if (inbuf[1] == 'd' && inbuf[2] == 'd') // Cadd7
      {
	inbuf.splice(0, 3); // consume the 'add'
	if (!(n = parse_number(inbuf)))
	  { err_mesg("No interval to add"); return -1; }
	if (rchord[n] > 0)
	  { err_mesg("interval " + n + " already in chord"); return -1; }

	// mark the added interval
	rchord[n] = TMAJOR;
	return 1;
      }

      // augmented interval, same as the cases below
      if (!(inbuf[1] == 'u' && inbuf[2] == 'g')) return 0;
      inbuf.shift(); inbuf.shift();

    case '+': case '#':  // augmented interval
      inbuf.shift();
      if (!(n = parse_number(inbuf))) n = 5; // defaults to augmented fifth
      rchord[n] = TAUGMENTED;
      return 1;

    case '-': case 'b':  // lowered interval
      inbuf.shift();
      if (!(n = parse_number(inbuf))) n = 5; // defaults to diminished fifth
      rchord[n] = TMINOR;
      return 1;
	 // OK, we _should_ 'minor' major interval and 'diminish' perfects...

    case 'd':  // diminished
      if (inbuf.length < 3) return 0;
      if (!(inbuf[1] == 'i' && inbuf[2] == 'm')) return 0;
      inbuf.splice(0, 3); // consume the 'dim'

      if (!(n = parse_number(inbuf)))
      { // no number specified, a diminished chord

	rchord[3] = TMINOR;
	rchord[5] = TDIMINISHED;
	rchord[7] = TDIMINISHED;
	return 0;
      }

      // there was an interval to diminish
      rchord[n] = TDIMINISHED;
      return 1;
    
    case '1': case '2': case '3': case '4': case '5':
    case '6': case '7': case '8': case '9':
      n = parse_number(inbuf);

      if (n >= 7) // we must also include a (minor) 7th
      {
	if (!rchord[7]) rchord[7] = TMINOR;
      }

      // add the interval, if it's not there already
      if (!rchord[n]) rchord[n] = TMAJOR;

      // a special case: '6/9'
      if (n == 6 && inbuf[0] == '/' && inbuf[1] == '9')
      {
	inbuf.shift(); inbuf.shift();
	if (!rchord[9]) rchord[9] = TMAJOR;
      }

      return 1;

    case 'm':   // Cmaj7
      if (inbuf.length < 3) return 0;
      if (!(inbuf[1] == 'a' && inbuf[2] == 'j')) return 0;
      inbuf.splice(0, 3); // consume the 'maj'

      if (!(n = parse_number(inbuf))) n = 7; // default to major 7th
      rchord[n] = TMAJOR;
      return 1;

    case 's':   // Csus
      if (inbuf.length < 3) return 0;
      if (!(inbuf[1] == 'u' && inbuf[2] == 's')) return 0;
      inbuf.splice(0, 3); // consume the 'sus'
      if (inbuf[0] == 'p') inbuf.shift();   // some write 'susp'

      if (!(n = parse_number(inbuf))) n = 4;
      if (!rchord[3])
	{ err_mesg('no third to suspend?'); return -1; }
      rchord[n] = rchord[3];
      rchord[3] = 0;
      return 1;

    case '/':  // might separate mods, might be a bass note
      if (inbuf[1] >= 'a' && inbuf[1] <= 'g') return 0;
      inbuf.shift();
      return parse_mod(inbuf, rchord); // parse the mod we find there

    case '(':  // parens for grouping
      inbuf.shift();
      if (!parse_mods(inbuf, rchord)) return -1;
      if (inbuf[0] != ')')
	{ err_mesg('unbalanced parentheses'); return -1; }
      inbuf.shift();
      return 1;
  
    default: return 0;
  }

  // fallthrough; I dunno
  return -1;
}

// see if we've specified a root/bass note
function parse_bass(inbuf, rchord)
{
  if (inbuf[0] != '/') return true;
  inbuf.shift();

  // only one is allowed
  if (rchord.bassnote != null)
  {
    err_mesg('bass note is already set :' + rchord.bassnote);
    return false;
  }

  if ((rchord.bassnote = parse_note(inbuf)) < 0) return false;

  return true;
}

// parse an interval number
function parse_number(inbuf)
{ var val = 0;

  if (inbuf[0] < '1' || inbuf[0] > '9') return 0;
  while (inbuf[0] >= '0' && inbuf[0] <= '9')
  { var ch;

    ch = inbuf.shift();
    val = val * 10 + +ch;
  }

  // limit return value to something moderately sensible
  if (val > 15)
    { err_mesg('disallowing intervals greater than a fifteenth'); return 0; }
  return val;
}
