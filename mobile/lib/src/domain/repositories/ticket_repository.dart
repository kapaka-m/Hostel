import 'package:hostel_mobile/src/data/api/api_client.dart';
import 'package:hostel_mobile/src/data/api/endpoints.dart';
import 'package:hostel_mobile/src/domain/models/ticket_comment_model.dart';
import 'package:hostel_mobile/src/domain/models/ticket_model.dart';
import 'package:hostel_mobile/src/domain/repositories/response_parser.dart';

class TicketRepository {
  final ApiClient _client;

  TicketRepository(this._client);

  Future<List<TicketModel>> fetchTickets({Map<String, dynamic>? filters}) async {
    final response = await _client.get(Endpoints.tickets, query: filters);
    final items = extractDataList(response.data);
    return items.map(TicketModel.fromJson).toList();
  }

  Future<TicketModel> createTicket({
    required String subject,
    required String description,
    String? category,
    String? priority,
    String? status,
    int? assignedTo,
    int? dormId,
  }) async {
    final payload = {
      'subject': subject,
      'description': description,
      if (category != null) 'category': category,
      if (priority != null) 'priority': priority,
      if (status != null) 'status': status,
      if (assignedTo != null) 'assigned_to': assignedTo,
      if (dormId != null) 'dorm_id': dormId,
    };
    final response = await _client.post(Endpoints.tickets, data: payload);
    return TicketModel.fromJson(ensureMap(response.data));
  }

  Future<TicketModel> updateTicket(int ticketId, Map<String, dynamic> updates) async {
    final response = await _client.put(Endpoints.ticketDetails(ticketId), data: updates);
    return TicketModel.fromJson(ensureMap(response.data));
  }

  Future<TicketCommentModel> addComment(int ticketId, String body) async {
    final response = await _client.post(Endpoints.ticketComments(ticketId), data: {
      'body': body,
    });
    return TicketCommentModel.fromJson(ensureMap(response.data));
  }
}
